<?php
namespace Liviano\Exceptions;
use Exception;
class ControllerMethodDoesntExistException extends Exception {
    public function __construct(string $metodo, $code = 0, Exception $previous = null) {
        parent::__construct("No existe método para manejar la solicitud", $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>