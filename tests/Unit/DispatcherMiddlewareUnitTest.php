<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Unit;

use Cocoon\Routing\Router;
use App\Handler\FinalHandler;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use App\Handler\MiddlewareRunner;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use FastRoute\Dispatcher as BaseDispatcher;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Cocoon\Routing\Middleware\DispatcherMiddleware;
use Cocoon\Routing\Tests\Controllers\TestController;
use Cocoon\Routing\Tests\Controllers\UserController;

class DispatcherMiddlewareUnitTest extends TestCase
{
    private Router $router;
    private DispatcherMiddleware $middleware;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;

    protected function setUp(): void
    {
        $this->router = Router::getInstance();
        //$this->router->clear();
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

    public function testProcessWithMethodNotAllowed(): void
    {
        $this->router->get('/test', [TestController::class, 'index']);
        $request = new ServerRequest([], [], '/test', 'POST');
        
        $response = $this->middleware->process($request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testProcessWithRouteParameters(): void
    {
        $this->router->get('/users/{id}', [UserController::class, 'show'])->name('user.show');
        $this->router->getRoutes();
        $uri = $this->router->url('user.show', ['id' => 123]);
        //$request = new ServerRequest([], [], $uri, 'GET');
        $request = (new ServerRequestFactory)->createServerRequest('GET', $uri);
        //$handler = new FinalHandler();
        //$middleware = new MiddlewareRunner($this->middleware, $handler);
        $response = $this->middleware->process($request, $this->handler);
        //$response = $middleware->run($request);
        $body = $response->getBody()->getContents();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('User Controller Show: 123', $body);
    }


    public function testProcessWithMultipleRouteParameters(): void
    {
        $this->router->get('/users/{id}/posts/{postId}', [UserController::class, 'showPost']);
        $this->router->getRoutes();
        //$request = new ServerRequest([], [], '/users/123/posts/456', 'GET');
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/users/123/posts/456');
        //$handler = new FinalHandler();
            //$middleware = new MiddlewareRunner($this->middleware, $handler);
        $response = $this->middleware->process($request, $this->handler);
        //$response = $middleware->run($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithOptionalParameters(): void
    {
        $this->router->get('/users/{id}', [UserController::class, 'showPost']);
        
        $request = new ServerRequest([], [], '/users/123', 'GET');
        //$handler = new FinalHandler();    
        //$middleware = new MiddlewareRunner($this->middleware, $handler);
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithCustomPatterns(): void
    {
        $this->router->get('/users/{id:[0-9]+}', [TestController::class, 'show']);
        $request = new ServerRequest([], [], '/users/abc', 'GET');
        
        $response = $this->middleware->process($request, $this->handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
} 