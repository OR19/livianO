<?php
namespace Liviano\Exceptions;
use Exception;
class RouteDependencyInjectionException extends Exception {
    public function __construct($dependenciaSolicitada, $code = 0, Exception $previous = null) {
        parent::__construct("No se ha proporcionado una inyección a la dependencia solicitada: \"$dependenciaSolicitada\"", $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>