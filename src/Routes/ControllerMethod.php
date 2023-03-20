<?php
namespace Liviano\Routes;

use Attribute;
use Liviano\Core\HttpMethods;

#[Attribute()]
/**
 * Atributo para los metadatos de los métodos de un RouteController
 */
class ControllerMethod {
    private string $path;
    private HttpMethods $method;
    private bool $recievePostJson;
    public function __construct( string $path, HttpMethods $method, bool $recievePostJson = false ) {
        $this->path = $path;
        $this->method = $method;
        $this->recievePostJson = $recievePostJson;
    }
    public function getPath(): string {
        return $this->path;
    }
    public function getMethod(): HttpMethods {
        return $this->method;
    }
    public function getRecievePostJson(): bool {
        return $this->recievePostJson;
    }
}
?>