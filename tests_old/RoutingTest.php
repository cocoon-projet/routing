<?php


namespace Routing;


use Cocoon\Routing\Router;
use PHPUnit\Framework\TestCase;

abstract class RoutingTest extends TestCase
{
    public $route;

    protected function setup() : void
    {
        $this->route = Router::getInstance();
    }
}