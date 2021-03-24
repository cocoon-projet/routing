<?php


namespace Routing;


use Cocoon\Routing\Router;
use PHPUnit\Framework\TestCase;

abstract class RoutingTest extends TestCase
{
    public $route;

    public function setup()
    {
        $this->route = Router::getInstance();
    }
}