<?php
namespace Liviano\Core;
/**
 * Encapsula la información de la solicitud del cliente
 * @author Orlando Martínez
 */
class Request {
    private HttpMethods $method;
    private array $data;
    private string $url;
    private float $seconds_start;
    private array $post_params;
    private array $get_params;


    public function __construct() {
        $this->method = HttpMethods::NOMETHOD;
        $this->url = '';
        $this->data = [];
        $this->post_params = [];
        $this->get_params = [];
        $this->seconds_start = -1;
    }
    /**
     * Obtiene los datos recibidos por el método POST
     * @return array Datos recibidos
     */
    public function getPostParams(): array {
        return $this->post_params;
    }
    /**
     * Agregar un elemento al arreglo PostParams
     * @param string $key Llave asociativa al valor
     * @param mixed $value Valor a insertar
     */
    public function addPostParam( string $key, mixed $value ): void {
        $this->post_params[ $key ] = $value;
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
     * @param HttpMethods $method Método HTTP
     */
    public function setMethod( HttpMethods $method ): void {
        $this->method = $method;
    }
    /**
     * Obtiene el método HTTP
     * @return HttpMethods Método HTTP
     */
    public function getMethod(): HttpMethods {
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