<?php

namespace Cocoon\Routing;

use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Gère les routes de l'application.
 *
 * Class Router
 * @package Cocoon\Routing
 */
class Router
{
    private static $instance = null;
    private $routeCollection;
    private $cachePath = '';
    private $namedRoute = [];
    protected $dispatcher;
    protected $defaultPrefix;
    protected $routeCache;
    protected $withUri= '';
    protected $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];
    private $rootBaseUrl;

    protected $ressources = [
        ['method' => 'GET', 'uri' => '', 'route' => '@index'],
        ['method' => 'GET', 'uri' => '/add', 'route' => '@add'],
        ['method' => 'POST', 'uri' => '/create', 'route' => '@create'],
        ['method' => 'GET', 'uri' => '/{id}', 'route' => '@show'],
        ['method' => 'GET', 'uri' => '/{id}/edit', 'route' => '@edit'],
        ['method' => 'PUT', 'uri' => '/{id}', 'route' => '@update'],
        ['method' => 'DELETE', 'uri' => '/{id}', 'route' => '@delete']
    ];

    private function __construct()
    {
        $this->defaultPrefix = '';
        $this->rootBaseUrl(trim(dirname(dirname($_SERVER['SCRIPT_NAME'])), DIRECTORY_SEPARATOR));
        $this->routeCollection = new RouteCollection();
    }

    private function __clone()
    {
    }

    public static function getInstance(): ?Router
    {
        if (is_null(self::$instance)) {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    public static function start(): ?Router
    {
        return static::getInstance();
    }


    public function get($uri, $handler): RouteBuilder
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post($uri, $handler): RouteBuilder
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put($uri, $handler): RouteBuilder
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function delete($uri, $handler): RouteBuilder
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function patch($uri, $handler): RouteBuilder
    {
        return $this->addRoute('PATCH', $uri, $handler);
    }

    public function head($uri, $handler): RouteBuilder
    {
        return $this->addRoute('HEAD', $uri, $handler);
    }

    public function any($uri, $handler): RouteBuilder
    {
        return $this->addRoute($this->methods, $uri, $handler);
    }

    /**
     * @param array $httpMethods
     * @param string $uri
     * @param array|string $handler
     * @return RouteBuilder
     */
    public function match(array $httpMethods, string $uri, $handler): RouteBuilder
    {
        return $this->addRoute($httpMethods, $uri, $handler);
    }

    /**
     * @param array|string $httpMethod
     * @param string $uri
     * @param array|string $handler
     * @return RouteBuilder
     */
    private function addRoute($httpMethod, string $uri, $handler): RouteBuilder
    {
        $route = $this->defaultPrefix . $uri;
        return $this->routeCollection->add($httpMethod, $route, $handler);
    }

    public function resource($name, $controller): RouteCollection
    {
        $ctrl = $controller;
        foreach ($this->ressources as $value) {
            $this->addRoute($value['method'], $name . $value['uri'], $ctrl . $value['route']);
        }
        return $this->routeCollection;
    }

    /**
     * @param string $prefix
     * @param callable $callback
     */
    public function group(string $prefix, callable $callback)
    {
        $this->defaultPrefix = '/' . trim($prefix, '/');
        $callback();
        $this->defaultPrefix = '';
    }

    private function getNamedRoutes(): array
    {
        return $this->namedRoute;
    }

    public function has($namedRoute): bool
    {
        if (isset($this->getNamedRoutes()[$namedRoute])) {
            return true;
        }
        return false;
    }

    public function name($namedRoute): string
    {
        if (array_key_exists($namedRoute, $this->getNamedRoutes())) {
            return $this->getNamedRoutes()[$namedRoute];
        }
    }

    protected function initRoutes(): array
    {
        $routes = [];
        foreach ($this->routeCollection->collection() as $built) {
            $routes[] = $built->route();
            $name = $built->getName();
            if (! empty($name)) {
                $this->namedRoute[$name] = $built->getNamedRoute($name);
            }
        }
        return $routes;
    }

    public function getRoutes(): array
    {
        return $this->initRoutes();
    }

    public function make($request): array
    {
        $request_uri = str_replace($this->rootBaseUrl, "", $request->getUri()->getPath());
        return $this->routeCollector()->dispatch($request->getMethod(), $request_uri);
    }

    public function cache($config, $path)
    {
        $this->routeCache = $config;
        $this->cachePath = $path;
        return $this;
    }

    public function rootBaseUrl($routeBaseUrl)
    {
        $this->rootBaseUrl = $routeBaseUrl;
    }

    protected function routeCollector(): \FastRoute\Dispatcher
    {
        $routes = $this->getRoutes();
        $routingCollection = function (RouteCollector $fastRouteCollector) use ($routes) {
            foreach ($routes as $route) {
                $fastRouteCollector->addRoute($route['httpMethod'], $route['route'], $route['handler']);
            }
        };

        if (!$this->routeCache) {
            $this->dispatcher = \FastRoute\simpleDispatcher($routingCollection);
        } else {
            $this->dispatcher = \FastRoute\cachedDispatcher(
                $routingCollection,
                ['cacheFile' => $this->cachePath . '/route.cache']
            );
        }
        return $this->dispatcher;
    }

    public function dispatch(ServerRequestInterface $request) : ResponseInterface
    {
        $route = $this->make($request);
        return (new RouteDispatcher($route))->dispatch();
    }
}
