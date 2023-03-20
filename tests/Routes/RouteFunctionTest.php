<?php

use Liviano\Core\HttpMethods;
use Liviano\Core\MiddlewareWrapper;
use Liviano\Core\Request;
use Liviano\Core\Response;
use Liviano\Exceptions\RouteDependencyInjectionException;
use Liviano\ParamMatchers\RouteParamDateMatcher;
use Liviano\ParamMatchers\RouteParamFloatMatcher;
use Liviano\ParamMatchers\RouteParamIntMatcher;
use Liviano\ParamMatchers\RouteParamMatcher;
use Liviano\ParamMatchers\RouteParamStringMatcher;
use Liviano\Routes\RouteFunction;
use PHPUnit\Framework\TestCase;

class RouteFunctionTest extends TestCase {
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
    public function testInvalidTypeParamRequired(): void {
        $this->expectException(RouteDependencyInjectionException::class);
        $rf = new RouteFunction('/{int:id}', function (float $id) {}, $this->paramMatchers);
        $this->request->setMethod(HttpMethods::GET);
        $this->request->setURL('/30');
        $rf->execute( $this->request, $this->response );
    }
    public function testInvalidNumberOfParametersRequired(): void {
        $this->expectException(RouteDependencyInjectionException::class);
        $rf = new RouteFunction('/{int:id}', function (int $id, float $number, string $text) {}, $this->paramMatchers);
        $this->request->setMethod(HttpMethods::GET);
        $this->request->setURL('/30');
        $rf->execute( $this->request, $this->response );
    }
    public function testRouteRegex(): void {
        $registedURL = "/api/elements/{int:id}/{float:numero}/{date:fecha}/{texto}";
        $rf = new RouteFunction($registedURL, function (int $id, float $numero, string $texto) {}, $this->paramMatchers);
        $actual = $rf->getRouteRegex();
        
        $paramIntDefinition = $this->paramIntMatcher->getRegexParamResult();
        $paramFloatDefinition = $this->paramFloatMatcher->getRegexParamResult();
        $paramDateDefinition = $this->paramDateMatcher->getRegexParamResult();
        $paramStringDefinition = $this->paramStringMatcher->getRegexParamResult();

        $expected = "/^\/api\/elements\/$paramIntDefinition\/$paramFloatDefinition\/$paramDateDefinition\/$paramStringDefinition$/";
        $this->assertEquals($expected, $actual);
    }
    public function testMiddlewaresUse(): void {
        $registedURL = "/";
        $rf = new RouteFunction($registedURL, function (Request $req, Response $resp) {
            $data = $req->getData();

            $this->assertNotNull($req, "Request Object no debe de ser nulo");
            $this->assertNotNull($resp, "Response Object no debe de ser nulo");

            $this->assertEquals(2, count($data), "Arreglo Data debe de ser de tamaño 2");

            $this->assertArrayHasKey('saludo', $data, "Arreglo data debe contener la llave 'saludo'");
            $this->assertArrayHasKey('numero', $data, "Arreglo data debe contener la llave 'numero'");
            $this->assertArrayNotHasKey('boolean', $data, "Arreglo data NO debe contener la llave 'saludo'");

            $this->assertEquals('mundo', $data['saludo'], "El valor de la clave 'saludo' debe de ser 'mundo'");
            $this->assertEquals(5, $data['numero'], "El valor de la clave 'numero' debe de ser 5");
        }, $this->paramMatchers);

        $rf->addMiddleware( new MiddlewareWrapper( function(Request $req, Response $resp){
            $req->setData('saludo', 'mundo');
            return true;
        }));
        $rf->addMiddleware( new MiddlewareWrapper( function(Request $req, Response $resp){
            $req->setData('numero', 5);
        }));
        $rf->addMiddleware( new MiddlewareWrapper( function(Request $req, Response $resp){
            $req->setData('boolean', false);
            return true;
        }));

        $rf->execute( $this->request, $this->response );
    }
}