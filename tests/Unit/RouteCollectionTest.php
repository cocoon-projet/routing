<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Cocoon\Routing\RouteCollection;
use Cocoon\Routing\Tests\Controllers\TestController;

class RouteCollectionTest extends TestCase
{
    private RouteCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new RouteCollection();
    }

    public function tearDown(): void
    {
        $this->collection->clear();
    }

    public function testAddRoute(): void
    {
        $route = $this->collection->add('GET', '/test', [TestController::class, 'index']);
        
        $this->assertNotNull($route);
        $this->assertEquals('GET', $route->route()['httpMethod']);
        $this->assertEquals('/test', $route->route()['route']);
        $this->assertEquals([TestController::class, 'index'], $route->route()['handler']);
    }

    public function testGetRoute(): void
    {
        $this->collection->add('GET', '/test', [TestController::class, 'index']);
        
        $route = $this->collection->collection()['/test'];
        
        $this->assertNotNull($route);
        $this->assertEquals('GET', $route->route()['httpMethod']);
        $this->assertEquals('/test', $route->route()['route']);
        $this->assertEquals([TestController::class, 'index'], $route->route()['handler']);
    }

    public function testGetRouteNotFound(): void
    {
        $route = $this->collection->collection()['/non-existent'] ?? null;
        
        $this->assertNull($route);
    }

    public function testGetRoutesByMethod(): void
    {
        $this->collection->add('GET', '/test1', [TestController::class, 'index']);
        $this->collection->add('GET', '/test2', [TestController::class, 'show']);
        $this->collection->add('POST', '/test3', [TestController::class, 'store']);
        
        $getRoutes = array_filter($this->collection->collection(), function($route) {
            return $route->route()['httpMethod'] === 'GET';
        });
        
        $this->assertCount(2, $getRoutes);
    }

    public function testGetAllRoutes(): void
    {
        $this->collection->add('GET', '/test1', [TestController::class, 'index']);
        $this->collection->add('GET', '/test2', [TestController::class, 'show']);
        $this->collection->add('POST', '/test3', [TestController::class, 'store']);
        
        $this->assertCount(3, $this->collection->collection());
    }

    public function testClearRoutes(): void
    {
        $this->collection->add('GET', '/test', [TestController::class, 'index']);
        $this->collection->clear();
        
        $this->assertCount(0, $this->collection->collection());
    }

    public function testRouteWithParameters(): void
    {
        $route = $this->collection->add('GET', '/users/{id}', [TestController::class, 'show']);
        
        $this->assertNotNull($route);
        $this->assertEquals('/users/{id:[0-9]+}', $route->route()['route']);
    }

    public function testRouteWithMultipleParameters(): void
    {
        $route = $this->collection->add('GET', '/users/{id}/posts/{postId}', [TestController::class, 'showPost']);
        
        $this->assertNotNull($route);
        $this->assertEquals('/users/{id:[0-9]+}/posts/{postId}', $route->route()['route']);
    }
} 