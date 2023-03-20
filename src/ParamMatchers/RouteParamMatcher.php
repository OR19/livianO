<?php
namespace Liviano\ParamMatchers;
/**
 * Permite identificar parámetros con formatos en el registro de una URL
 * @author Orlando Martínez
 */
abstract class RouteParamMatcher {
    /**
     * Expresión regular para identificar el formato solicitado al registrar la URL
     */
    protected string $regexParamDefinition;
    /**
     * Expresión regular para identificar los valores en la URL solicitada
     */
    protected string $regexParamResult;
    /**
     * Instancia un objeto 
     * @param string $regexParamDefinition Expresión regular para identificar el formato solicitado al registrar la URL
     * @param string $regexParamResult Patrón para identificar los valores en la URL solicitada
     */
    public function __construct( string $regexParamDefinition, string $regexParamResult ) {
        $this->regexParamDefinition = $regexParamDefinition;
        $this->regexParamResult = $regexParamResult;
    }
    /**
     * Obtiene la expresión regular para identificar los valores en la URL solicitada
     */
    public function getRegexParamResult(): string {
        return $this->regexParamResult;
    }
    /**
     * Convierte un valor de la ruta a su valor indicado
     * @param string $valor Valor de entrada
     * @return mixed Valor convertido
     */
    public abstract function convert( string $valor ): mixed; 
    /**
     * Verifica si hace match con la definición de registro
     * @param string $valor Valor de entrada
     * @return bool True si cumple con la definición
     */
    public abstract function matchDefinition( string $valor ): bool; 
    /**
     * Extrae el nombre del parámetro
     * @param string $valor Valor con el formato de registro
     * @return string Nombre de la variable a registrar
     */
    public abstract function extractParamName( string $valor ): string; 
}
?>