<?php


namespace Cocoon\Routing\Facade;

use Cocoon\Routing\Router;

class Route
{

    public static function __callStatic($name, $arguments)
    {
        $instance = Router::start();
        return $instance->$name(...$arguments);
    }
}
