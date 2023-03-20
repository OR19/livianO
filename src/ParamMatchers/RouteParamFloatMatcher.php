<?php 
namespace Liviano\ParamMatchers;
/**
 * Matcher para parametros de rutas de tipo float
 * @author Orlando Martínez
 */
class RouteParamFloatMatcher extends RouteParamMatcher{
    public function __construct() {
        parent::__construct("/\{float:[a-zA-Z]\w*\}/", "\d+\.\d+");
    }
    public function convert( string $valor ): mixed {
        if( !preg_match( "/^". $this->regexParamResult . "$/", $valor ) ) return false;
        return floatval( $valor );
    }
    public function matchDefinition( string $valor ): bool {
        return preg_match( $this->regexParamDefinition, $valor );
    }
    public function extractParamName( string $valor ): string {
        return substr( $valor, 7, strlen( $valor ) - 8 );
    }
}
?>