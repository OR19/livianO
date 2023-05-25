<?php
namespace Liviano\Routes;

use Liviano\Core\HttpMethods;
use Liviano\Core\MiddlewareWrapper;
use Liviano\Core\Request;
use Liviano\Core\Response;
use Liviano\Exceptions\RouteDependencyInjectionException;
use Liviano\Exceptions\RouteFormatException;
use Liviano\ParamMatchers\RouteParamMatcher;
use ReflectionFunction;
use ReflectionParameter;
/**
 * Provee una abstracción para la creación de rutas
 */
abstract class Route {
    /**
     * @var RouteParamMatcher[]
     */
    protected array $paramMatchers = [];
    /**
     * @var MiddlewareWrapper[]
     */
    protected array $middlewares;
    /**
     * @var callable[]
     */
    protected array $dependenciesFactory;

    /**
     * Instancia un objeto ruta
     * @param string $routePath Ruta a registrar
     * @param RouteParamMatcher[] $paramMatchers Matchers para los parámetros
     */
    public function __construct( array $paramMatchers = [], array $dependenciesFactory = [] ) {
        // if( !$this->isRoutePathCorrect( $routePath ) )
        //     throw new RouteFormatException();
        // $this->routePathRegistered = $routePath;
        $this->paramMatchers = $paramMatchers;
        $this->middlewares = [];
        $this->dependenciesFactory = [];
        // $this->routePathMatcher = $this->generateRoutePathMatcher( $this->cleanRoutePath( $routePath ), $matches );
        // $this->variablesURL = $matches;
    }
    //Métodos
    /**
     * Limpia una ruta
     * @param string $routePath Ruta URL
     * @return string Ruta limpiada
     */
    protected function cleanRoutePath( string $routePath ): string {
        if( $routePath !== "/" )
            $routePath = rtrim( $routePath, " \t\n\r\0\x0B/" );
        return $routePath;
    }
    /**
     * Realiza la ejecución de la ruta
     * @param Request $request Objeto que contiene la Información de la solicitud
     * @param Response $response Objeto que gestiona la respuesta
     * @param array $dependenciasFactory Dependencias inyectadas solicitadas por la ruta
     */
    abstract public function execute( Request $request, Response $response, array $dependenciasFactory): void;
    protected function getJsonPost(): array {
        $json = file_get_contents('php://input');
        return json_decode( $json, true ) ?? [];
    }
    protected function executeMiddlewares( Request $request, Response $response ): bool {
        foreach ( $this->middlewares as $middleware ) {
            if( !$middleware->execute( $request, $response ) ) return false;
        }
        return true;
    }
    /**
     * Agrega una función middleware la cual se procesará antes de ejecutarse la ruta
     * @param MiddlewareWrapper $middlewareFunction Middleware que procesará la solicitud antes de ejecutar la ruta
     * @return Route Devuelve la misma ruta
     */
    public function addMiddleware( MiddlewareWrapper $middlewareFunction ): Route {
        array_push( $this->middlewares, $middlewareFunction );
        return $this;
    }
    /**
     * Genera el matcher de la ruta a partir del formato de ruta registrada
     * @param string $routePath Ruta registrada
     * @param ParamPathMatchOccurence[] $matches Matches de las variables de la ruta
     * @return string Expresión regular que permite identificar la ruta
     */
    protected function generateRoutePathMatcher( string $routePath, array &$matches ): string {
        $routePath = trim( $routePath, " \t\n\r\0\x0B/" );
        $splitedPath = explode( '/', $routePath );
        $newSplitedPath = [];
        $matches = [];
        foreach( $splitedPath as $indexPath => $valuePath ) {
            $foundMatch = false;
            foreach( $this->paramMatchers as $paramMatcher ) {
                if( $paramMatcher->matchDefinition( $valuePath ) ) {
                    $matches[ $paramMatcher->extractParamName( $valuePath ) ] = new ParamPathMatchOccurence( $indexPath, $paramMatcher->extractParamName( $valuePath ), $paramMatcher );
                    array_push( $newSplitedPath, $paramMatcher->getRegexParamResult() );
                    $foundMatch = true;
                    break;
                }
            }
            if( !$foundMatch ){
                array_push( $newSplitedPath, $valuePath );
            }
        }
        return "/^\/" . join( "\/", $newSplitedPath ) . "$/";
    }
    /**
     * Verifica si el formato de la ruta es correcta
     * @param string $routePath Ruta registrada
     * @return bool Devuelve true si el formato de la ruta es correcto
     */
    public static function isRoutePathCorrect( string $routePath ): bool {
        $open = false;
        for( $i = 0; $i < strlen( $routePath ); $i++ ) {
            if( $routePath[ $i ] == "}" && !$open ) return false;
            if( $routePath[ $i ] == "{" && $open ) return false;
            if( $routePath[ $i ] == "{" && !$open ) {
                $open = true;
                continue;
            }
            if( $routePath[ $i ] == "}" && $open ){
                $open = false;
                continue;
            }
        }
        return !$open;
    }
    protected function getParams( callable $function ): array {
        $reflection = new ReflectionFunction( $function );
        return array_map( function(ReflectionParameter $parameter) {
            return [ 'name' => $parameter->getName(), 'type' => $parameter->getType()->getName() ];
        }, $reflection->getParameters() );
    }
    protected function generateInjectableParams( callable $function, Request $request, Response $response, array $paramPathMatches = [] ): array {
        $requestedURL = explode( '/',  trim($request->getURL(), " \t\n\r\0\x0B/") );
        $parametros = $this->getParams( $function );
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
}
?>