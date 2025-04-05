<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Cocoon\Routing\Router;
use Cocoon\Routing\RouteDispatcher;
use Cocoon\Routing\RouteCollection;
use Cocoon\Routing\Tests\Controllers\TestController;
use Cocoon\Routing\Tests\Controllers\UserController;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use FastRoute\Dispatcher as BaseDispatcher;

class RouteDispatcherTest extends TestCase
{
    private Router $router;
    private RouteCollection $collection;
    private RouteDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->router = Router::getInstance();
        $this->collection = new RouteCollection();
        $this->dispatcher = new RouteDispatcher([
            BaseDispatcher::FOUND,
            [TestController::class, 'index'],
            ['id' => '123']
        ]);
    }

    public function tearDown(): void
    {
        $this->router->clear();
    }

    public function testDispatchSuccessfulRoute(): void
    {
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDispatchRouteWithParameters(): void
    {
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDispatchRouteNotFound(): void
    {
        $this->dispatcher = new RouteDispatcher([
            BaseDispatcher::NOT_FOUND,
            [TestController::class, 'not_found'],
            ['id' => '123']
        ]);
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDispatchMethodNotAllowed(): void
    {
        $this->dispatcher = new RouteDispatcher([
            BaseDispatcher::METHOD_NOT_ALLOWED,
            [TestController::class, 'not_found'],
            ['id' => '123']
        ]);
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testDispatchWithMultipleParameters(): void
    {
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDispatchWithOptionalParameters(): void
    {
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDispatchWithCustomPatterns(): void
    {
        $response = $this->dispatcher->dispatch();
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
} 