<?php
namespace Liviano\Exceptions;
use Exception;
class RouteFormatException extends Exception {
    public function __construct($message = "La ruta tiene un formato incorrecto", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>