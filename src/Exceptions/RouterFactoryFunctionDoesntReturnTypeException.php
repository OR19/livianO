<?php
namespace Liviano\Exceptions;
use Exception;
class RouterFactoryFunctionDoesntReturnTypeException extends Exception {
    public function __construct($code = 0, Exception $previous = null) {
        parent::__construct("La función de inyeccion de dependencia no retorna un valor", $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>