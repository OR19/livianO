<?php 
namespace Liviano\ParamMatchers;
/**
 * Matcher para parametros de rutas de tipo string
 * @author Orlando Martínez
 */
class RouteParamStringMatcher extends RouteParamMatcher{
    public function __construct() {
        parent::__construct("/\{[a-zA-Z]\w*\}/", "\w+");
    }
    public function convert( string $valor ): mixed {
        return strval( $valor );
    }
    public function matchDefinition( string $valor ): bool {
        return preg_match( $this->regexParamDefinition, $valor );
    }
    public function extractParamName( string $valor ): string {
        return substr( $valor, 1, strlen( $valor ) - 2 );
    }
}
?>