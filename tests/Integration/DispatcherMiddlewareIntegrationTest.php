<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Integration;

use Cocoon\Routing\Router;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use FastRoute\Dispatcher as BaseDispatcher;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Cocoon\Routing\Middleware\DispatcherMiddleware;
use Cocoon\Routing\Tests\Controllers\TestController;
use Cocoon\Routing\Tests\Controllers\UserController;

class DispatcherMiddlewareIntegrationTest extends TestCase
{
    private Router $router;
    private DispatcherMiddleware $middleware;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;

    protected function setUp(): void
    {
        $this->router = Router::getInstance();
        $this->middleware = new DispatcherMiddleware($this->router);
        $this->request = new ServerRequest([], [], '/test', 'GET');
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    public function tearDown(): void
    {
        $this->router->clear();
    }

    public function testProcessWithFoundRoute(): void
    {
        $this->router->get('/test', [TestController::class, 'index']);
        
        $response = $this->middleware->process($this->request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithNotFoundRoute(): void
    {
        $request = new ServerRequest([], [], '/not-found', 'GET');
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
/*
    public function testProcessWithMethodNotAllowed(): void
    {
        //$this->router->get('/test', [TestController::class, 'index']);
        $this->router->match(['NONE'], '/test', [TestController::class, 'index']);
        $this->router->getRoutes();

        $request = (new ServerRequestFactory)->createServerRequest('NONE', '/test');
       
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testProcessWithRouteParameters(): void
    {
        $this->router->get('/users/{id}', [UserController::class, 'show']);
        //$request = new ServerRequest([], [], '/users/123', 'GET');
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/test', ['id' => '123']);
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithMultipleRouteParameters(): void
    {
        $this->router->get('/users/{id}/posts/{postId}', [UserController::class, 'showPost']);
  
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/test', ['id' => '123', 'postId' => '456']);
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithOptionalParameters(): void
    {
        $this->router->get('/users/{id}/posts/{postId}', [UserController::class, 'showPost']);
        //$request = new ServerRequest([], [], '/users/123/posts', 'GET');
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/test/123/posts/456');
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithCustomPatterns(): void
    {
        $this->router->get('/users/{id:[0-9]+}', [UserController::class, 'show']);
        //$request = new ServerRequest([], [], '/users/abc', 'GET');
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/test', ['id' => 'abc']);
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testProcessWithNamedRoutes(): void
    {
        $this->router->get('/users/{id}', [UserController::class, 'show'])->name('user.show');
        //$request = new ServerRequest([], [], '/users/123', 'GET');
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/test', ['id' => '123']);    
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithResourceRoutes(): void
    {
        $this->router->resource('posts', TestController::class);
        $this->router->getRoutes();
        $routes = [
            ['GET', '/posts', [TestController::class, 'index']],
            ['GET', '/posts/create', [TestController::class, 'create']],
            ['POST', '/posts', [TestController::class, 'store']],
            ['GET', '/posts/{id}', [TestController::class, 'show']],
            ['GET', '/posts/{id}/edit', [TestController::class, 'edit']],
            ['PUT', '/posts/{id}', [TestController::class, 'update']],
            ['DELETE', '/posts/{id}', [TestController::class, 'destroy']]
        ];

        foreach ($routes as [$method, $path, $handler]) {
            //$request = new ServerRequest([], [], $path, $method);
            $request = (new ServerRequestFactory)->createServerRequest($method, $path);
            $response = $this->middleware->process($request, $this->handler);
            
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }*/
} 