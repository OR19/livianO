<?php
namespace Liviano\Routes;

use Liviano\Core\HttpMethods;
use Liviano\Core\MiddlewareWrapper;
use Liviano\Core\Request;
use Liviano\Core\Response;
use Liviano\Exceptions\RouteFormatException;
use Liviano\Exceptions\RouterFactoryFunctionDoesntReturnTypeException;
use Liviano\Exceptions\RouterRegisterRouteException;
use Liviano\ParamMatchers\RouteParamMatcher;
use ReflectionClass;
use ReflectionFunction;
/**
 * Provee funcionalidades de manejos de rutas
 */
class Router {
    private static float $secondsStart = 0;
    private static array $routeParamMatchers = [];
    private static array $dependenciesFactory = [];
    /**
     * @var MiddlewareWrapper[]
     */
    private static array $globalBeforeMiddlewares = [];
    private static array $globalAfterMiddlewares = [];
    private static array $routes = [];
    private static $handleNotFoundFunction = null;
    /**
     * Agrega param matchers
     */
    public static function addRouteParamMatcher( RouteParamMatcher ...$paramMatchers ): void {
        foreach ( $paramMatchers as $pm ) self::$routeParamMatchers[] = $pm;
    }
    /**
     * Agrega una función generadora de dependencia
     * @param callable $factoryFunction Función que genera un valor de dependencia y lo retorna
     */
    public static function addInyectableDependency( callable $factoryFunction ): void {
        $reflection = new ReflectionFunction( $factoryFunction );
        if( $reflection->getReturnType() == null ) {
            throw new RouterFactoryFunctionDoesntReturnTypeException();
        }
        else{
            self::$dependenciesFactory[ $reflection->getReturnType()->getName() ] = $factoryFunction;
        }
    }

    /**
     * Registra una ruta que será escuchada con el método HTTP GET
     * @param string $path Formato de ruta a escuchar
     * @param callable $handlerFunction Función que se manejará la petición
     */
    public static function get( string $path, callable $handlerFunction ): Route {
        return Router::addRouteFunction($path, HttpMethods::GET, $handlerFunction);
    }
    /**
     * Registra una ruta que será escuchada con el método HTTP POST
     * @param string $path Formato de ruta a escuchar
     * @param callable $handlerFunction Función que se manejará la petición
     */
    public static function post( string $path, callable $handlerFunction ): Route {
        return Router::addRouteFunction($path, HttpMethods::POST, $handlerFunction);
    }
    /**
     * Registra una ruta que será escuchada con el método HTTP PUT
     * @param string $path Formato de ruta a escuchar
     * @param callable $handlerFunction Función que se manejará la petición
     */
    public static function put( string $path, callable $handlerFunction ): Route {
        return Router::addRouteFunction($path, HttpMethods::PUT, $handlerFunction);
    }
    /**
     * Registra una ruta que será escuchada con el método HTTP DELETE
     * @param string $path Formato de ruta a escuchar
     * @param callable $handlerFunction Función que se manejará la petición
     */
    public static function delete( string $path, callable $handlerFunction ): Route {
        return Router::addRouteFunction($path, HttpMethods::DELETE, $handlerFunction);
    }
    /**
     * Registra una ruta que será escuchada por un controlador, el controlador escuchará todos los métodos
     * @param string $routePath Formato de ruta a escuchar
     * @param string $controllerClassName Nombre de la clase que se usará para generar el controlador
     */
    public static function useController( string $routePath, string $controllerClassName ): Route {
        if( !class_exists( $controllerClassName ) )
            throw new RouterRegisterRouteException("No existe la clase: $controllerClassName");
        $reflectionClass = new ReflectionClass( $controllerClassName );
        if( !$reflectionClass->getParentClass() === 'Liviano\Routes\RouteController' ) {
            throw new RouterRegisterRouteException('El controlador a registrar necesita heredar de Liviano\Routes\RouteController');
        }
        $route = new $controllerClassName($routePath, self::$routeParamMatchers, self::$dependenciesFactory);
        $regexs = $route->getRoutesRegex();
        /**
         * @var array $value
         */
        foreach( $regexs as $key => $value ) {
            if ( $key === 'matches' ) continue;
            if ( array_key_exists( $key, self::$routes ) )
                throw new RouterRegisterRouteException("Ya existe la ruta que se intenta registrar: $key");
            foreach( $value as $method => $val ) {
                Router::$routes[ $key ][ $method ] = $route;
            }
        }
        return $route;
    }
    /**
     * Registra una función Middleware que se ejecutará antes de ejecutar una ruta
     * @param MiddlewareWrapper $middleware Función Middleware
     */
    public static function useBeforeMiddlewareFunction( MiddlewareWrapper $middleware ): void {
        array_push( self::$globalBeforeMiddlewares, $middleware );
    }
    /**
     * Registra una función Middleware que se ejecutará después de ejecutar una ruta
     * @param MiddlewareWrapper $middleware Función Middleware
     */
    public static function useAfterMiddlewareFunction( MiddlewareWrapper $middleware ): void {
        array_push( self::$globalAfterMiddlewares, $middleware );
    }
    /**
     * Agrega una función que manejará el error 404
     * @param callable $handler Función
     */
    public static function addNotFoundHandler(callable $handler): void {
        self::$handleNotFoundFunction = $handler;
    }
    /**
     * Ejecuta el ruteo por medio de una ruta
     * @param string $routePath Ruta que indica el recurso a solicitar
     */
    public static function execute( string $routePath ): void {
        self::$secondsStart = time();
        //Creación de objetos request y response
        $request = Router::createRequestObject( $routePath );
        $response = new Response();
        //Ejecutar los middleware globales (before)
        if( !self::executeBeforeMiddlewares( $request, $response) ) return;
        //Buscar la ruta que coincida con la solicitud
        $routeNotFound = true;
        foreach( self::$routes as $routeMatch => $routeMethods  ) {
            //Si la ruta solicitada hace match con la registrada
            if(preg_match_all($routeMatch, $routePath) > 0 && array_key_exists( $request->getMethod()->value, $routeMethods)) {
                $route = $routeMethods[$request->getMethod()->value];
                //Si la ruta la gestiona un controlador
                if( $route instanceof RouteController ){
                    $route->execute( $request, $response);
                    $routeNotFound = false;
                    break;
                }
                //Si la ruta la gestionan funciones
                elseif( $route instanceof RouteFunction ) {
                    $route->execute( $request, $response);
                    $routeNotFound = false;
                    break;
                }
            }
        }
        if( $routeNotFound ) self::notFound($request, $response);
        self::executeAfterMiddlewares( $request, $response );
    }
    /**
     * Ejecuta los middlewares (before)
     * @param Request $request Objeto Request
     * @param Response $response Objeto Response
     * @return bool Devuélve True si los middlewares pasaron con éxito
     */
    private static function executeBeforeMiddlewares( Request $request, Response $response ): bool {
        foreach ( self::$globalBeforeMiddlewares as $middleware ) {
            if( !$middleware->execute( $request, $response ) ) return false;
        }
        return true;
    }
    /**
     * Ejecuta los middlewares (after)
     * @param Request $request Objeto Request
     * @param Response $response Objeto Response
     * @return bool Devuélve True si los middlewares pasaron con éxito
     */
    private static function executeAfterMiddlewares( Request $request, Response $response ): bool {
        foreach ( self::$globalAfterMiddlewares as $middleware ) {
            if( !$middleware->execute( $request, $response ) ) return false;
        }
        return true;
    }
    /**
     * Agrega una función con el método al conjunto de rutas registradas
     * @param string $path Formato de ruta que indica el recurso a solicitar
     * @param HttpMethods $method Método por el cual se solicitará
     * @param callable $handlerFunction Función encargada del manejo de la solicitud
     */
    private static function addRouteFunction( string $path, HttpMethods $method, callable $handlerFunction ): Route {
        if( !Route::isRoutePathCorrect( $path ) ) throw new RouteFormatException();
        $route = new RouteFunction( $path, $handlerFunction, Router::$routeParamMatchers, Router::$dependenciesFactory );
        if( array_key_exists( $route->getRouteRegex(), Router::$routes ) ){
            if( Router::$routes[ $route->getRouteRegex() ][ $method->value ] instanceof RouteController ){
                throw new RouterRegisterRouteException("Ya existe un controlador asociado a esta ruta");
            }
            Router::$routes[ $route->getRouteRegex() ][ $method->value ] = $route;
            return $route;
        }
        else{
            Router::$routes[ $route->getRouteRegex() ] = [ $method->value => $route ];
            return $route;
        }
    }
    /**
     * Genera una respuesta 404 Not Found
     * @param Request Objeto Request
     * @param Response Objeto Response
     */
    public static function notFound( Request $request, Response $response ): void {
        http_response_code(404);
        if( self::$handleNotFoundFunction != null ) {
            $f = new RouteFunction( $request->getURL(), self::$handleNotFoundFunction, self::$routeParamMatchers, self::$dependenciesFactory );
            $f->execute( $request, $response );
        }
    }
    /**
     * Crea un Request Object
     * @param string $url Ruta que indica el recurso a solicitar
     */
    private static function createRequestObject( $url = '' ): Request {
        $request = new Request();
        $request->setMethod( HttpMethods::fromString( $_SERVER[ 'REQUEST_METHOD' ] ) );
        $request->setURL( $url );
        // foreach( $_GET as $getKey => $getValue ) {
        //     $request->addGetParam( $getKey, $getValue );
        // }
        foreach( $_POST as $postKey => $postValue ) {
            $request->addPostParam( $postKey, $postValue );
        }
        $request->setSecondsStart( self::$secondsStart );
        return $request;
    }
}
?>