<?php
namespace Liviano\Routes;

use Liviano\Core\HttpMethods;
use Liviano\Core\Request;
use Liviano\Core\Response;
use Liviano\Exceptions\ControllerMethodDoesntExistException;
use Liviano\Exceptions\RouteDependencyInjectionException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
/**
 * Permite manejar la lógica de un controlador
 */
abstract class RouteController extends Route {

    private array $routesRegex;
    private string $basePath;
    public function __construct( string $basePath, array $paramMatchers = [], array $dependenciesFactory = [] ) {
        parent::__construct( $paramMatchers, $dependenciesFactory );
        $this->basePath = $basePath;
        $this->routesRegex = $this->generateRoutesRegex();
    }
	/**
	 * Realiza la ejecución de la ruta
	 *
	 * @param Request $request Objeto que contiene la Información de la solicitud
	 * @param Response $response Objeto que gestiona la respuesta
	 */
	public function execute(Request $request, Response $response, array $dependenciasFactory): void {
        $matches = [];
        $options = [];
        $reflection = $this->getFunction( $request->getURL(), $request->getMethod(), $matches, $options);
        if( $reflection == null ) {
            throw new ControllerMethodDoesntExistException("");
        }

        if( $options['postJson'] ) {
            $json = $this->getJsonPost();
            foreach( $json as $itemIndex => $itemValue )
                $request->addPostParam( $itemIndex, $itemValue );
        }

        $this->executeMiddlewares($request, $response);
        $this->dependenciesFactory = $dependenciasFactory;
        $params = $this->generateInjectableParamsMethod( $reflection, $request, $response, $matches);
        $reflection->invokeArgs($this, $params);
	}
    /**
     * Obtiene el método que manejará una solicitud
     * @param string $url Expresión regular que representa la dirección URL 
     * @param HttpMethods $method Método http  de la solicitud
     * @param array $matches Coincidencias de los parámetros url solicitados
     * @param array $options Opciones del método que manejará la ruta
     * @return ?ReflectionMethod Método que manejará la solicitud
     */
    private function getFunction( string $url, HttpMethods $method, array &$matches, array &$options = [] ): ?ReflectionMethod {
        foreach( $this->routesRegex as $rutaRegex => $rutaValue ) {
            if( preg_match( $rutaRegex, $url ) && array_key_exists($method->value, $rutaValue)) {
                $matches = $rutaValue['matches'];
                $options =$rutaValue[$method->value]['options'];
                return $rutaValue[$method->value]['function'];
            }
        }
        return null;
    }
    /**
     * Obtiene los parámetros de un método
     * @param ReflectionMethod Reflección del método
     * @return array Información de los parámetros
     */
    private function getParamsReflectionMethod( ReflectionMethod $reflection ): array {
        return array_map( function(ReflectionParameter $parameter) {
            return [ 'name' => $parameter->getName(), 'type' => $parameter->getType()->getName() ];
        }, $reflection->getParameters() );
    }
    /**
     * Genera los parámetros que se inyectarán en el método
     * @param ReflectionMethod Reflección del método
     * @param Request Objeto Request
     * @param Response Objeto Response
     * @param array $paramPathMatches Coincidencias de los parámetros url solicitados
     * @return array Parámetros a inyectar
     * @throws RouteDependencyInjectionException Si un parámetro solicitado no cuenta con una inyección
     */
    private function generateInjectableParamsMethod( ReflectionMethod $reflection, Request $request, Response $response, array $paramPathMatches = [] ): array {
        $requestedURL = explode( '/',  trim($request->getURL(), " \t\n\r\0\x0B/") );
        $parametros = $this->getParamsReflectionMethod( $reflection );
        $parametrosInyectables = [];
        foreach( $parametros as $parametro ) {
            //Clases e interfaces
            if( class_exists( $parametro[ 'type' ] ) || interface_exists( $parametro[ 'type' ] ) ) {
                if( $parametro[ 'type' ] == 'Liviano\Core\Request' )
                    $parametrosInyectables[] = $request;
                elseif( $parametro[ 'type' ] == 'Liviano\Core\Response' )
                    $parametrosInyectables[] = $response;
                elseif( array_key_exists( $parametro[ 'type' ], $this->dependenciesFactory ) ) {
                    $factoryFunction = $this->dependenciesFactory[ $parametro[ 'type' ] ];
                    $parametrosInyectables[] = $factoryFunction();
                }
            }
            //Parámetros de ruta
            elseif( array_key_exists( $parametro['name'], $paramPathMatches ) ) {
                $parametrosInyectables[] = $paramPathMatches[ $parametro['name'] ]
                                                    ->getMatcher()
                                                    ->convert( $requestedURL[  $paramPathMatches[ $parametro['name'] ]->getIndex() ] );
            }
            else{
                throw new RouteDependencyInjectionException( "{$parametro['type']} {$parametro['name']}" );
            }
        }
        return $parametrosInyectables;
    }
    /**
     * Genera las expresiónes regulares de las rutas que maneja
     * @return array Expresiónes regulares
     */
    private function generateRoutesRegex(): array {
        $paths = $this->getControllerPaths();
        $regexs = [];
        foreach( $paths as $pathKey => $pathValue ) {
            $paramPathMatches = [];
            $regex = $this->generateRoutePathMatcher( $this->basePath . $this->cleanRoutePath( $pathKey ), $paramPathMatches );
            $regexs[ $regex ] = $pathValue;
            $regexs[ $regex ][ 'matches' ] = $paramPathMatches;
        }
        return $regexs;
    }
    /**
     * Obtiene las expresiónes regulares de las rutas que maneja
     * @return string[]
     */
    public function getRoutesRegex(): array {
        return $this->routesRegex;
    }
    /**
     * Obtiene la información de los métodos que manejarán la ruta
     * @return array<string, callable>
     */
    private function getControllerPaths(): array {
        $reflector = new ReflectionClass( $this );
        $reflectionMethods = $reflector->getMethods();
        $paths = [];
        foreach( $reflectionMethods as $reflectionMethod ) {
            $attributes = $reflectionMethod->getAttributes();
            foreach( $attributes as $attribute ) {
                if ( $attribute->getName() === ControllerMethod::class ) {
                    /** @var ControllerMethod */
                    $controllerMethod =  $attribute->newInstance();
                    if( array_key_exists( $controllerMethod->getPath(), $paths ) ) {
                        $paths[ $controllerMethod->getPath() ][$controllerMethod->getMethod()->value ] = [
                            'function' => $reflectionMethod,
                            'options' => [
                                'postJson' => $controllerMethod->getRecievePostJson()
                            ]
                        ];
                    }
                    else {
                        $paths[ $controllerMethod->getPath() ] = [
                            $attribute->getArguments()[1]->value => [
                                'function' => $reflectionMethod,
                                'options' => [
                                    'postJson' => $controllerMethod->getRecievePostJson()
                                ]
                            ]
                        ];
                    }
                    break;
                }
            }
        }
        return $paths;
    }
}
?>