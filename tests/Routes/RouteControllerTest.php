<?php

use Liviano\Core\HttpMethods;
use Liviano\Core\MiddlewareWrapper;
use Liviano\Core\Request;
use Liviano\Core\Response;
use Liviano\Exceptions\ControllerMethodDoesntExistException;
use Liviano\Exceptions\RouteDependencyInjectionException;
use Liviano\ParamMatchers\RouteParamDateMatcher;
use Liviano\ParamMatchers\RouteParamFloatMatcher;
use Liviano\ParamMatchers\RouteParamIntMatcher;
use Liviano\ParamMatchers\RouteParamMatcher;
use Liviano\ParamMatchers\RouteParamStringMatcher;
use Liviano\Routes\ControllerMethod;
use Liviano\Routes\ParamPathMatchOccurence;
use Liviano\Routes\RouteController;
use Liviano\Routes\RouteFunction;
use PHPUnit\Framework\TestCase;

class RouteControllerTest extends TestCase {
    /**
     * @var RouteParamMatcher[]
     */
    private array $paramMatchers;
    private RouteParamIntMatcher $paramIntMatcher;
    private RouteParamFloatMatcher $paramFloatMatcher;
    private RouteParamDateMatcher $paramDateMatcher;
    private RouteParamStringMatcher $paramStringMatcher;
    private Request $request;
    private Response $response;
    public function setUp(): void {
        $this->paramIntMatcher =  new RouteParamIntMatcher();
        $this->paramFloatMatcher = new RouteParamFloatMatcher();
        $this->paramDateMatcher = new RouteParamDateMatcher();
        $this->paramStringMatcher = new RouteParamStringMatcher();
        $this->paramMatchers = [
            $this->paramIntMatcher,
            $this->paramFloatMatcher,
            $this->paramDateMatcher,
            $this->paramStringMatcher
        ];
        $this->request = new Request();
        $this->response = new Response();
    }
    public function testMethodDoesnExist(): void {
        $this->expectException(ControllerMethodDoesntExistException::class);
        $rc = $this->generateControllerInvalidTypeParamRequired('/estudiantes', $this->paramMatchers, []);
        $this->request->setMethod(HttpMethods::PUT);
        $this->request->setURL('/estudiantes/6');
        $rc->execute( $this->request, $this->response );
    }
    public function testInvalidTypeParamRequired(): void {
        $this->expectException(RouteDependencyInjectionException::class);
        $rc = $this->generateControllerInvalidTypeParamRequired('/estudiantes', $this->paramMatchers, []);
        $this->request->setMethod(HttpMethods::GET);
        $this->request->setURL('/estudiantes/6');
        $rc->execute( $this->request, $this->response );
    }
    public function testInvalidNumberOfParametersRequired(): void {
        $this->expectException(RouteDependencyInjectionException::class);
        $rc = $this->generateControllerInvalidNumberOfParametersRequired('/estudiantes', $this->paramMatchers, []);
        $this->request->setMethod(HttpMethods::GET);
        $this->request->setURL('/estudiantes/6');
        $rc->execute( $this->request, $this->response );
    }
    public function testRouteRegex(): void {
        $rc = $this->generateControllerMultipleMethods('/estudiantes', $this->paramMatchers, []);
        $actual = $rc->getRoutesRegex();
        $expected = [
            '/^\/estudiantes$/' => [
                'GET' => [
                    'function' => null,//new ReflectionMethod(),
                    'options' => [],
                ],
                'matches' => []
            ],
            '/^\/estudiantes\/\d+$/' => [
                'GET' => [
                    'function' => null,//new ReflectionMethod(),
                    'options' => [],
                ],
                'DELETE' => [
                    'function' => null,//new ReflectionMethod(),
                    'options' => [],
                ],
                'PUT' => [
                    'function' => null,//new ReflectionMethod(),
                    'options' => [],
                ],
                'matches' => [
                    'id' => new ParamPathMatchOccurence(1, 'id', $this->paramIntMatcher)
                ],
            ]
        ];
        $this->assertEquals(count($expected), count($actual), 'El tamaño es diferente');
        /**
         * @var array $actual
         */
        foreach($expected as $regex => $methods) {
            $this->assertArrayHasKey( $regex, $actual, 'No cuenta con la expresión regular');
            /**
             * @var string $method
             */
            foreach( $methods as $method => $arr ) {
                $this->assertArrayHasKey( $method, $actual[$regex], 'No cuenta con el método' );
                if($method != 'matches') {
                    $this->assertArrayHasKey( 'function', $actual[$regex][$method], 'No tiene definida la llave "function"' );
                    $this->assertArrayHasKey( 'options', $actual[$regex][$method], 'No tiene definida la llave "options"' );
                }
                else {
                    foreach( $arr as $matchName => $matchValue ) {
                        $this->assertArrayHasKey( $matchName, $actual[$regex]['matches'], 'No tiene el match' );
                    }
                }
                
            }
        }
        // $this->assertEquals($expected, $actual);
    }
    public function testMiddlewaresUse(): void {
        $rc = new class('/estudiantes', $this->paramMatchers, []) extends RouteController {
            public function __construct( string $path, array $paramMatchers = [], array $dependenciesFactory = [] ) {
                parent::__construct( $path, $paramMatchers, $dependenciesFactory );
            }
            #[ControllerMethod('/', HttpMethods::GET)]
            public function obtenerEstudiantes(Request $req): void {

            }
        };

        $rc->addMiddleware( new MiddlewareWrapper( function(Request $req, Response $resp){
            $req->setData('saludo', 'mundo');
            return true;
        }));
        $rc->addMiddleware( new MiddlewareWrapper( function(Request $req, Response $resp){
            $req->setData('numero', 5);
        }));
        $rc->addMiddleware( new MiddlewareWrapper( function(Request $req, Response $resp){
            $req->setData('boolean', false);
            return true;
        }));
        $this->request->setMethod(HttpMethods::GET);
        $this->request->setURL('/estudiantes');
        $rc->execute( $this->request, $this->response );
        $data = $this->request->getData();

        $this->assertNotNull($this->request, "Request Object no debe de ser nulo");
        $this->assertNotNull($this->request, "Response Object no debe de ser nulo");

        $this->assertEquals(2, count($data), "Arreglo Data debe de ser de tamaño 2");

        $this->assertArrayHasKey('saludo', $data, "Arreglo data debe contener la llave 'saludo'");
        $this->assertArrayHasKey('numero', $data, "Arreglo data debe contener la llave 'numero'");
        $this->assertArrayNotHasKey('boolean', $data, "Arreglo data NO debe contener la llave 'saludo'");

        $this->assertEquals('mundo', $data['saludo'], "El valor de la clave 'saludo' debe de ser 'mundo'");
        $this->assertEquals(5, $data['numero'], "El valor de la clave 'numero' debe de ser 5");
    }
    public function generateControllerInvalidTypeParamRequired(string $path, array $paramMatchers = [], array $dependenciesFactory = []): RouteController {
        $obj = new class($path, $paramMatchers, $dependenciesFactory) extends RouteController {
            public function __construct( string $path, array $paramMatchers = [], array $dependenciesFactory = [] ) {
                parent::__construct( $path, $paramMatchers, $dependenciesFactory );
            }
            #[ControllerMethod('/{int:id}', HttpMethods::GET)]
            public function obtenerEstudiante(float $numero): void {
            }
        };
        return $obj;
    }
    public function generateControllerInvalidNumberOfParametersRequired(string $path, array $paramMatchers = [], array $dependenciesFactory = []): RouteController {
        $obj = new class($path, $paramMatchers, $dependenciesFactory) extends RouteController {
            public function __construct( string $path, array $paramMatchers = [], array $dependenciesFactory = [] ) {
                parent::__construct( $path, $paramMatchers, $dependenciesFactory );
            }
            #[ControllerMethod('/{int:id}', HttpMethods::GET)]
            public function obtenerEstudiante(float $numero, string $texto): void {
            }
        };
        return $obj;
    }
    public function generateControllerMultipleMethods(string $path, array $paramMatchers = [], array $dependenciesFactory = []): RouteController {
        $obj = new class($path, $paramMatchers, $dependenciesFactory) extends RouteController {
            public function __construct( string $path, array $paramMatchers = [], array $dependenciesFactory = [] ) {
                parent::__construct( $path, $paramMatchers, $dependenciesFactory );
            }
            #[ControllerMethod('/', HttpMethods::GET)]
            public function obtenerEstudiantes(): void {
            }
            #[ControllerMethod('/{int:id}', HttpMethods::GET)]
            public function obtenerEstudiante(int $id): void {
            }
            #[ControllerMethod('/{int:id}', HttpMethods::DELETE)]
            public function eliminarEstudiante(int $id): void {
            }
            #[ControllerMethod('/{int:id}', HttpMethods::PUT)]
            public function actualizarEstudiante(int $id): void {
            }
        };
        return $obj;
    }
    public function generateControllerExpectedDataMiddlewares(string $path, array $paramMatchers = [], array $dependenciesFactory = []): RouteController {
        $obj = new class($path, $paramMatchers, $dependenciesFactory) extends RouteController {
            public function __construct( string $path, array $paramMatchers = [], array $dependenciesFactory = [] ) {
                parent::__construct( $path, $paramMatchers, $dependenciesFactory );
            }
            #[ControllerMethod('/', HttpMethods::GET)]
            public function obtenerEstudiantes(Request $req): void {
                
            }
        };
        return $obj;
    }
}