<?php

namespace Cocoon\Routing\Middleware;

use Cocoon\Dependency\DI;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

use FastRoute\Dispatcher as BaseDispatcher;

/**
 * Class DispatcherMiddleware
 * @package Cocoon\Routing\Middleware
 */
class DispatcherMiddleware implements MiddlewareInterface
{
    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) :ResponseInterface
    {
        $response = $handler->handle($request);
        $routeInfo = $this->router->make($request);

        if ($routeInfo[0] === BaseDispatcher::NOT_FOUND) {
            $response = new HtmlResponse('ERROR 404', 404);
        } elseif ($routeInfo[0] === BaseDispatcher::METHOD_NOT_ALLOWED) {
            $response = new HtmlResponse('ERROR 405', 405);
        } else {
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            if (is_string($handler)) {
                $params = explode('@', $handler);
                $response = $this->resolveHandler($params, $vars);
            } elseif (is_array($handler)) {
                $response = $this->resolveHandler($handler, $vars);
            } else {
                $handle = call_user_func_array($handler, $vars);
                if ($handle instanceof ResponseInterface) {
                    $response = $handle;
                } elseif (is_string($handle)) {
                    $response = new HtmlResponse($handle);
                }
            }
        }
        return $response;
    }

    private function resolveHandler($handler, $vars)
    {
        $response = '';
        $action = isset($handler[1]) ? $handler[1] : 'index';

        if (!class_exists($handler[0])) {
            throw new RuntimeException('Le controller: ' . $handler[0] . ' n\'éxiste pas');
        }

        $response_controller = DI::make($handler[0], $action, $vars);

        if ($response_controller instanceof ResponseInterface) {
            $response = $response_controller;
        } else {
            if (is_string($response_controller)) {
                return new HtmlResponse($response_controller);
            }
        }
        return $response;
    }
}
