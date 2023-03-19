<?php 
namespace Liviano\ParamMatchers;
/**
 * Matcher para parametros de rutas de tipo int
 * @author Orlando Martínez
 */
class RouteParamIntMatcher extends RouteParamMatcher{
    public function __construct() {
        parent::__construct("/\{int:[a-zA-Z]\w*\}/", "\d+");
    }
    public function convert( string $valor ): mixed {
        if( !preg_match( "/^". $this->regexParamResult . "$/", $valor ) ) return false;
        return intval( $valor );
    }
    public function matchDefinition( string $valor ): bool {
        return preg_match( $this->regexParamDefinition, $valor );
    }
    public function extractParamName( string $valor ): string {
        return substr( $valor, 5, strlen( $valor ) - 6 );
    }
}
?>