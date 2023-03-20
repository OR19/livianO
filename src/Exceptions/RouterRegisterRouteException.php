<?php
namespace Liviano\Exceptions;
use Exception;
class RouterRegisterRouteException extends Exception {
    public function __construct(string $message = "Error al registrar una ruta", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>