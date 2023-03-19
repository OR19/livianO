<?php 
namespace Liviano\ParamMatchers;
/**
 * Matcher para parametros de rutas de tipo date
 * @author Orlando Martínez
 */
class RouteParamDateMatcher extends RouteParamMatcher{
    public function __construct() {
        parent::__construct("/\{date:[a-zA-Z]\w*\}/","\d{4}-\d{2}-\d{2}");
    }
    public function convert( string $valor ): mixed {
        if( !preg_match( "/^". $this->regexParamResult . "$/", $valor ) ) return false;
        return $this->strToDate( $valor );
    }
    public function matchDefinition( string $valor ): bool {
        return preg_match( $this->regexParamDefinition, $valor );
    }
    public function extractParamName( string $valor ): string {
        return substr( $valor, 6, strlen( $valor ) - 7 );
    }
    /**
     * Convierte un string con formato yyyy-mm-dd a un arreglo con los datos de la fecha
     * @param string $str Cadena representando a una fecha en el formato yyyy-mm-dd
     * @return ?array Información de la fecha
     */
    private function strToDate ( string $str ): ?array {
        if( !preg_match( "/^". $this->regexParamResult . "$/", $str ) ) return null;
        $datos_fecha = explode( '-', $str );
        $year = $datos_fecha[0];
        $mes = $datos_fecha[1];
        $dia = $datos_fecha[2];
        if ( !checkdate( $mes, $dia, $year ) ) return null;
        return getdate( strtotime( "$year-$mes-$dia" ) );
    }
}
?>