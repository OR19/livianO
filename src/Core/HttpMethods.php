<?php
namespace Liviano\Core;
/**
 * Enumera los diferentes métodos HTTP
 * @author Orlando Martínez
 */
enum HttpMethods : string {
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case NOMETHOD = 'NOMETHOD';
    public static function fromString( string $method ): HttpMethods {
        switch( $method ) {
            case 'GET': return HttpMethods::GET;
            case 'POST': return HttpMethods::POST;
            case 'PUT': return HttpMethods::PUT;
            case 'DELETE': return HttpMethods::DELETE;
            case 'PATCH': return HttpMethods::PATCH;
            case 'OPTIONS': return HttpMethods::OPTIONS;
            default: return HttpMethods::NOMETHOD;
        }
    }
    public function toString(): string {
        return $this->value;
    }
}