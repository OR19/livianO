<?php
namespace Liviano\Core;
/**
 * Wrapper para funciones middleware
 * @author Orlando Martínez
 */
class MiddlewareWrapper {
    /**
     * @param callable(Request $req, Response $resp):bool $function
     */
    private $func;
    /**
     * @param callable(Request $req, Response $resp):bool $function
     */
    public function __construct( callable $function ) {
        $this->func = $function;
    }
    /**
     * Ejecuta la función middleware
     * @param Request $req Objeto Request
     * @param Response $res Objeto response
     */
    public function execute( Request $req, Response $res ): bool {
        $result = call_user_func_array( $this->func, [ $req, $res ] );
        if( gettype( $result ) == 'boolean' ) return $result;
        return false;
    }
}
?>