<?php
namespace Liviano\Core;
/**
 * Enumera los diferentes métodos HTTP
 * @author Orlando Martínez
 */
enum ERequestMethods : string {
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case NOMETHOD = 'NOMETHOD';
    public static function fromString( string $method ): ERequestMethods {
        switch( $method ) {
            case 'GET': return ERequestMethods::GET;
            case 'POST': return ERequestMethods::POST;
            case 'PUT': return ERequestMethods::PUT;
            case 'DELETE': return ERequestMethods::DELETE;
            case 'PATCH': return ERequestMethods::PATCH;
            case 'OPTIONS': return ERequestMethods::OPTIONS;
            default: return ERequestMethods::NOMETHOD;
        }
    }
    public function toString(): string {
        return $this->value;
    }
}