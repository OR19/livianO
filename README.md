# LivianO
LivianO es un framework sencillo, inicialmente para proyectos sencillos


## Instalación

Comienza agregando liviano a tus dependencias en tu archivo composer.json
```json
{
  "repositories":[
    {
      "type": "vcs",
      "url": "https://github.com/OR19/livianO"
    }
  ],
  "require": {
    "or19/liviano": "dev-main"
  }
}
```
Recuerda hacer la actualización de los paquetes
```sh
composer update
```
## Guía rápida
### Configuración básica del `Router`
Comienza agregando los ParamMatchers los cuales te permiten obtener parámetros al momento de registrar una ruta

```php
<?php
  use Liviano\Routes\Router;
  use Liviano\ParamMatchers\RouteParamIntMatcher;
  use Liviano\ParamMatchers\RouteParamFloatMatcher;
  use Liviano\ParamMatchers\RouteParamDateMatcher;
  use Liviano\ParamMatchers\RouteParamStringMatcher;
  Router::addRouteParamMatcher( 
    new RouteParamIntMatcher(), 
    new RouteParamFloatMatcher(), 
    new RouteParamDateMatcher(), 
    new RouteParamStringMatcher() 
  );
?>
```
### Registra una ruta
El `Router` te permite registrar funciones que manejen una dirección
```php
<?php
  use Liviano\Routes\Router;
  Router::get('/', function(){
    echo "¡Hola mundo, estoy usando LivianO ❤!";
  });
?>
```
### Ejecuta el `Router`
El `Router` cuenta con un método `execute` el cual recibe un string indicando la ruta que se debe de manejar, en este parámetro deberás colocar la dirección recibida
```php
<?php
  use Liviano\Routes\Router;
  Router::execute('/');
?>
```
#### Ejemplo
Juntando lo previamente visto, en tu archivo de ejemplo tendrías el siguiente código
```php
<?php
   use Liviano\Routes\Router;
  use Liviano\ParamMatchers\RouteParamIntMatcher;
  use Liviano\ParamMatchers\RouteParamFloatMatcher;
  use Liviano\ParamMatchers\RouteParamDateMatcher;
  use Liviano\ParamMatchers\RouteParamStringMatcher;
  Router::addRouteParamMatcher( 
    new RouteParamIntMatcher(), 
    new RouteParamFloatMatcher(), 
    new RouteParamDateMatcher(), 
    new RouteParamStringMatcher() 
  );
  Router::get('/', function(){
    echo "¡Hola mundo, estoy usando LivianO ❤!";
  });
  Router::execute('/');
?>
```


## Documentación
Te recomendamos continuar con la [documentación](https://linktodocumentation), para ver las funcionalidades que te ofrece LivianO

