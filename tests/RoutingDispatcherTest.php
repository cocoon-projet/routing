<?php


namespace Routing;


use Cocoon\Routing\Facade\Route;
use Cocoon\Routing\Middleware\DispatcherMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingDispatcherTest extends TestCase
{
    public function delegate()
    {
        return $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    public function testRouteFound()
    {

        $request  = $this->createMock(ServerRequestInterface::class);
        $uri      = $this->createMock(UriInterface::class);

        $uri
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn('/show/15')
        ;

        $request
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn('GET')
        ;

        $request
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($uri)
        ;

        Route::match(['GET', 'HEAD'], '/ctrl', [BlogController::class, 'index'])->name('ctrl_test');
        Route::any('/show/{id}',  [BlogController::class, 'show'])->name('ctrl_test_show');
        $response = Route::dispatch($request);
        //------------------- Middleware Dispatcher
        $middleware = new DispatcherMiddleware(Route::start());
        $response_middleware = $middleware->process($request, $this->delegate());
        $this->assertInstanceOf(ResponseInterface::class, $response_middleware);
        $this->assertSame(200, $response_middleware->getStatusCode());
        $this->assertSame('test_show 15', $response_middleware->getBody()->getContents());
        //---------------------------------------------
        $this->assertTrue(Route::has('ctrl_test'));
        $this->assertSame(Route::name('ctrl_test'), '/ctrl');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('test_show 15', $response->getBody()->getContents());
    }

    public function testRouteNotFound()
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $uri      = $this->createMock(UriInterface::class);

        $uri
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn('/not-found')
        ;

        $request
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn('GET')
        ;

        $request
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($uri)
        ;
        $response = Route::dispatch($request);
        //------------------- Middleware Dispatcher
        $middleware = new DispatcherMiddleware(Route::start());
        $response_middleware = $middleware->process($request, $this->delegate());
        $this->assertInstanceOf(ResponseInterface::class, $response_middleware);
        $this->assertSame(404, $response_middleware->getStatusCode());
        //---------------------------------------
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $uri      = $this->createMock(UriInterface::class);

        $uri
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn('/ctrl')
        ;

        $request
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn('BAD')
        ;

        $request
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($uri)
        ;
        $response = Route::dispatch($request);
        //------------------ Middleware Dispatcher
        $middleware = new DispatcherMiddleware(Route::start());
        $response_middleware = $middleware->process($request, $this->delegate());
        $this->assertSame(405, $response_middleware->getStatusCode());
        //------------------------------------------------
        $this->assertSame(405, $response->getStatusCode());
    }
}