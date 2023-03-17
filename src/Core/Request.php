<?php
namespace Liviano\Core;
/**
 * Encapsula la información de la solicitud del cliente
 * @author Orlando Martínez
 */
class Request {
    private ERequestMethods $method;
    private array $data;
    private string $url;
    private float $seconds_start;


    public function __construct() {
        $this->method = ERequestMethods::fromString( $_SERVER[ 'REQUEST_METHOD' ] );
        $this->url = '';
        $this->data = [];
        $this->seconds_start = -1;
    }
    /**
     * Calcula los segundos transcurridos a partir de los segundos
     * @return float La diferencia entre los segundos start y el tiempo actual
     */
    public function SecondsLapsed(): float {
        if( $this->seconds_start < 0 ) return 0;
        $now = microtime( true );
        return $now - $this->seconds_start;
    }
    /**
     * Establece el método HTTP
     * @param ERequestMethods $method Método HTTP
     */
    public function setMethod( ERequestMethods $method ): void {
        $this->method = $method;
    }
    /**
     * Obtiene el método HTTP
     * @return ERequestMethods Método HTTP
     */
    public function getMethod(): ERequestMethods {
        return $this->method;
    }
    /**
     * Establece la dirección URL
     * @param string $url Dirección URL
     */
    public function setURL( string $url ): void {
        $this->url = $url;
    }
    /**
     * Obtiene la dirección URL
     * @return string Dirección URL
     */
    public function getURL(): string {
        return $this->url;
    }
    /**
     * Obtiene la información del servidor
     * @return array Información del servidor
     */
    public function getServerData(): array {
        return $_SERVER;
    }
    /**
     * Obtiene las cabeceras
     * @return array Cabeceras
     */
    public function getRequestHeaders(): array {
        return apache_request_headers();
    }
    /**
     * Establece un elemento al arreglo data
     * @param mixed data Elemento a agregar
     */
    public function setData( string $key, mixed $value ): void {
        $this->data[ $key ] = $value;
    }
    /**
     * Obtiene el arreglo data
     * @return array Arreglo data
     */
    public function getData(): array {
        return $this->data;
    }
    /**
     * Establece el tiempo de inicio
     * @param float $seconds Segundos de inicio
     */
    public function setSecondsStart( float $seconds ): void {
        $this->seconds_start = $seconds;
    }
    /**
     * Obtienen el timepo de inicio
     * @return float Segundos de inicio
     */
    public function getSecondsStart(): float {
        return $this->seconds_start;
    }
}
?>