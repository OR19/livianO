<?php
namespace Liviano\Routes;

use Liviano\Core\Request;
use Liviano\Core\Response;
/**
 * Permite manejar una ruta con una función
 */
class RouteFunction extends Route {
    /**
     * @var array<string,ParamPathMatchOccurence>
     */
    private array $paramPathMatches;
    private bool $canRecievePostJson;
    private string $pathRegisted;
    /**
     * @var callable
     */
    private $function;
    /**
     * Instancia un objeto
     * @param string $pathRegisted Ruta para registrar
     * @param callable $function Función que manejará la solicitud
     * @param array $paramMatchers Param Matchers para manejar los parámetros indicados en la url
     * @param array $dependenciesFactory Funciones generadoras de dependencias
     */
    public function __construct( string $pathRegisted, callable $function, array $paramMatchers = [], array $dependenciesFactory = [] ) {
        parent::__construct( $paramMatchers, $dependenciesFactory );
        $this->pathRegisted = $pathRegisted;
        $this->function = $function;
        $this->paramPathMatches = [];
        $this->canRecievePostJson = false;
    }
    /**
     * Establece que recivirá un JSON por el método POST
     */
    public function recievePostJson(): void {
        $this->canRecievePostJson = true;
    }
    public function execute( Request $request, Response $response ): void {
        if( $this->canRecievePostJson ) {
            $json = $this->getJsonPost();
            foreach( $json as $itemIndex => $itemValue )
                $request->addPostParam( $itemIndex, $itemValue );
        }

        $this->executeMiddlewares($request, $response);

        $injectablesParams = $this->generateInjectableParams( $this->function, $request, $response, $this->paramPathMatches );

        $f = $this->function;
        call_user_func_array( $f, $injectablesParams );
    }
    /**
     * Obtiene la expresión regular que coincide con la ruta registrada
     * @return string Expresión regular
     */
    public function getRouteRegex(): string {
        $regex = $this->generateRoutePathMatcher( $this->cleanRoutePath( $this->pathRegisted ), $this->paramPathMatches );
        return $regex;
    }
}
?>