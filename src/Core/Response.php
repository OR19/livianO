<?php
namespace Liviano\Core;
/**
 * Ofrece operaciones básicas para ofrecer respuestas
 * @author Orlando Martínez
 */
class Response {
    /**
     * Establece el código HTTP
     * @param int $code Código de estado HTTP
     */
    public function sendHttpCode( int $code ): void {
        http_response_code($code);
    }
    /**
     * Realiza una redirección a una URL
     * @param string $url Dirección URL
     * @param int $statusCode [Opcional] Código de estado HTTP
     */
    public function redirect(string $url, int $statusCode = 303): void {
       header("Location: $url", true, $statusCode);
    }
    /**
     * Establece la cabecera Content-Type: application/json; charset=utf-8
     */
    public function headerApplicationJson(): void {
        header('Content-Type: application/json; charset=utf-8');
    }
    /**
     * Realiza un echo de un json como respuesta
     * @param mixed $json Elemento JSON a aplicar json_encode
     * @param int $statusCode [Opcional] Código de estado de la respuesta
     */
    public function sendJSON( mixed $json, int $statusCode = 200 ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode( $json );
    }
}
?>