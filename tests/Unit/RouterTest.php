<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Unit;

use Cocoon\Routing\Router;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\RequestFactory;
use Psr\Http\Message\ResponseInterface;
use FastRoute\Dispatcher as BaseDispatcher;
use Laminas\Diactoros\ServerRequestFactory;
use Cocoon\Routing\Tests\Controllers\PostController;
use Cocoon\Routing\Tests\Controllers\TestController;
use Cocoon\Routing\Tests\Controllers\UserController;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = Router::getInstance();
    }

    public function tearDown(): void
    {
        $this->router->clear();
    }

    public function testGetRoute(): void
    {
        $this->router->get('/test', [TestController::class, 'index']);
        
        $request = new ServerRequest([], [], '/test', 'GET');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRoute(): void
    {
        $this->router->post('/test', [TestController::class, 'store']);
   
        $request = new ServerRequest([], [], '/test', 'POST');  
        $response = $this->router->dispatch($request);

        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPutRoute(): void
    {
        $this->router->put('/test/{id}', [TestController::class, 'update']);
        
        $request = new ServerRequest([], [], '/test/1', 'PUT');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteRoute(): void
    {
        $this->router->delete('/test/{id}', [TestController::class, 'destroy']);
        
        $request = new ServerRequest([], [], '/test/1', 'DELETE');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteWithParameters(): void
    {
        $this->router->get('/users/{id}', [UserController::class, 'show']);
        
        $request = new ServerRequest([], [], '/users/123', 'GET');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteWithMultipleParameters(): void
    {
        $this->router->get('/users/{id}/posts/{postId}', [UserController::class, 'showPost']);
        
        $request = new ServerRequest([], [], '/users/123/posts/456', 'GET');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteWithOptionalParameters(): void
    {
        $this->router->get('/users/{id}/posts/{postId}', [UserController::class, 'showPost']);
        
        $request = new ServerRequest([], [], '/users/123/posts/456', 'GET');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteWithCustomPatterns(): void
    {
        $this->router->get('/users/{id:[0-9]+}', [UserController::class, 'show']);
        
        $request = new ServerRequest([], [], '/users/abc', 'GET');
        $response = $this->router->dispatch($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testNamedRoutes(): void
    {
        $this->router->get('/users/{id}', [UserController::class, 'show'])->name('user.show');
        $this->router->getRoutes();
        $url = $this->router->url('user.show', ['id' => 123]);
        $this->assertEquals('/users/123', $url);
    }

    public function testNamedRouteWithMissingParameters(): void
    {
        
        $this->router->get('/users/{id}', [UserController::class, 'show'])->name('user.show');
        $this->router->getRoutes();
        $this->expectException(\InvalidArgumentException::class);
        $this->router->url('user.show');
    }

    public function testNamedRouteWithOptionalParameters(): void
    {
        $this->router->get('/users/{id}/posts/{postId}', [UserController::class, 'showPost'])->name('user.post');
        $this->router->getRoutes();
        
        // Test avec le paramètre postId
        $url = $this->router->url('user.post', ['id' => 123, 'postId' => 456]);
        $this->assertEquals('/users/123/posts/456', $url);
        
        // Test sans le paramètre postId
        //$url = $this->router->url('user.post', ['id' => 123]);
        //$this->router->getRoutes();
        //$this->assertEquals('/users/123/posts', $url);
    }
/*
    public function testResourceCreatesAllRoutes(): void
    {
        $this->router->resource('posts', PostController::class);
        $this->router->getRoutes();
        $routes = [
            ['GET', '/post', 'Cocoon\Routing\Tests\Controllers\PostController@index'],
            ['GET', '/post/add', 'Cocoon\Routing\Tests\Controllers\PostController@add'],
            ['POST', '/post/create', 'Cocoon\Routing\Tests\Controllers\PostController@create'],
            ['GET', '/post/show/{id}', 'Cocoon\Routing\Tests\Controllers\PostController@show'],
            ['GET', '/post/{id}/edit', 'Cocoon\Routing\Tests\Controllers\PostController@edit'],
            ['PUT', '/post/update/{id}', 'Cocoon\Routing\Tests\Controllers\PostController@update'],
            ['DELETE', '/post/delete/{id}', 'Cocoon\Routing\Tests\Controllers\PostController@delete']
        ];

        foreach ($routes as [$method, $path, $handler]) {
            //$request = new ServerRequest([], [], $path, $method);
            $request = (new ServerRequestFactory)->createServerRequest($method, $path);
            $response = $this->router->dispatch($request);
            
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }
*/
} 