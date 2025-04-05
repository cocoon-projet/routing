<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Cocoon\Routing\RouteCollection;
use Cocoon\Routing\RouteBuilder;
use Cocoon\Routing\Tests\Controllers\TestController;

class RouteBuilderTest extends TestCase
{
    private RouteCollection $collection;
    private RouteBuilder $builder;

    protected function setUp(): void
    {
        $this->collection = new RouteCollection();
        $this->builder = new RouteBuilder('GET', '/test', [TestController::class, 'index']);
    }

    public function tearDown(): void
    {
        $this->collection->clear();
    }

    public function testRouteBuilderCreation(): void
    {
        $this->assertEquals('GET', $this->builder->route()['httpMethod']);
        $this->assertEquals('/test', $this->builder->route()['route']);
        $this->assertEquals([TestController::class, 'index'], $this->builder->route()['handler']);
    }

    public function testNamedRoute(): void
    {
        $route = $this->builder->name('test.index');
        
        $this->assertEquals('test.index', $route->route()['name']);
    }

    public function testRouteWithParameters(): void
    {
        $builder = new RouteBuilder('GET', '/users/{id}', [TestController::class, 'show']);
        
        $this->assertEquals('/users/{id:[0-9]+}', $builder->route()['route']);
    }

    public function testRouteWithMultipleParameters(): void
    {
        $builder = new RouteBuilder('GET', '/users/{id}/posts/{postId}', [TestController::class, 'showPost']);
        
        $this->assertEquals('/users/{id:[0-9]+}/posts/{postId}', $builder->route()['route']);
    }

    public function testRouteWithOptionalParameters(): void
    {
        $builder = new RouteBuilder('GET', '/users/{id}/posts/{postId}', [TestController::class, 'showPost']);
        
        $this->assertEquals('/users/{id:[0-9]+}/posts/{postId}', $builder->route()['route']);
    }

    public function testRouteWithCustomPatterns(): void
    {
        $builder = new RouteBuilder('GET', '/users/{id:[0-9]+}', [TestController::class, 'show']);
        
        $this->assertEquals('/users/{id:[0-9]+}', $builder->route()['route']);
    }

} 