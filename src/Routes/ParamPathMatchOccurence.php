<?php
namespace Liviano\Routes;

use Liviano\ParamMatchers\RouteParamMatcher;
/**
 * Encapsula la información de una coincidencia de parámetro de ruta
 */
class ParamPathMatchOccurence {
    private int $index;
    private string $nombre;
    private RouteParamMatcher $matcher;
    public function __construct( int $index, string $nombre, RouteParamMatcher $matcher ) {
        $this->index = $index;
        $this->nombre = $nombre;
        $this->matcher = $matcher;
    }
    public function getIndex(): int {
        return $this->index;
    }
    public function getNombre(): string {
        return $this->nombre;
    }
    public function getMatcher(): RouteParamMatcher {
        return $this->matcher;
    }
}
?>