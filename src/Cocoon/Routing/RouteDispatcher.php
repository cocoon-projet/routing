<?php


namespace Cocoon\Routing;

use Cocoon\Dependency\DI;
use FastRoute\Dispatcher as BaseDispatcher;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class RouteDispatcher
{
    private $routeInfo;

    public function __construct($routeInfo)
    {
        $this->routeInfo = $routeInfo;
    }

    public function dispatch(): ResponseInterface
    {
        $response = null;
        if ($this->routeInfo[0] === BaseDispatcher::NOT_FOUND) {
            $response = new HtmlResponse('ERROR 404', 404);
        } elseif ($this->routeInfo[0] === BaseDispatcher::METHOD_NOT_ALLOWED) {
            $response = new HtmlResponse('ERROR 405', 405);
        } else {
            $handler = $this->routeInfo[1];
            $vars = $this->routeInfo[2];
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
