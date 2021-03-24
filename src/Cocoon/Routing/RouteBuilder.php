<?php

namespace Cocoon\Routing;

/**
 * Enregistre une route pour l'application
 *
 * Class RouteBuilder
 * @package Cocoon\Routing
 */
class RouteBuilder
{
    private $httpMethod;
    private $uri;
    private $handler;
    private $routeGetName = [];
    private $name = '';
    private $host = '';
    private $scheme = '';
    private $port = '';
    protected $aliasPatterns = [
        ':num' => ':[0-9]+',
        ':str' => ':[a-zA-Z]+',
        ':alnum' => ':[a-zA-Z0-9]+',
        ':slug' => ':[a-z0-9\-]+',
        '{id}' => '{id:[0-9]+}',
        '{slug}' => '{slug:[a-z0-9\-]+}'
    ];

    protected $addPattern = [];

    /**
     * RouteBuilder constructor.
     * @param string|array $httpMethod
     * @param string $uri
     * @param string|callable $handler
     */
    public function __construct($httpMethod, string $uri, $handler)
    {
        $this->httpMethod = $httpMethod;
        $this->uri = $uri;
        $this->handler = $handler;
    }
    /**
     * definit un alias pour une route
     *
     * @param string $routeName
     * @return $this
     */
    public function name($routeName = ''): RouteBuilder
    {
        $this->name = $routeName;
        if (! empty($this->name)) {
            $this->routeGetName[$this->name] = $this->resolveUri();
        }
        return $this;
    }

    /**
     * retourne le nom de la route, si definit.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retourne les paramètres de la route enregistée
     *
     * @return array
     */
    public function route(): array
    {
        return ['httpMethod' => $this->httpMethod,
            'route' => $this->resolveUri(),
            'handler' => $this->handler,
            'name' => $this->name
            ];
    }

    /**
     * @param $host
     * @return $this
     */
    public function host($host): RouteBuilder
    {
        $this->host = '//' . $host;
        return $this;
    }

    /**
     * @param $scheme
     * @return $this
     */
    public function scheme($scheme): RouteBuilder
    {
        $this->scheme = $scheme . ':';
        return $this;
    }

    /**
     * @param numeric $port
     * @return $this
     */
    public function port($port): RouteBuilder
    {
        if (is_numeric($port)) {
            $this->port = ':' . $port;
        }
        return $this;
    }

    /**
     * @param string $arg
     * @param $pattern
     * @return $this
     */
    public function with(string $arg, $pattern): RouteBuilder
    {
        $this->addPattern['{' . $arg . '}'] = '{' . $arg . ':' . $pattern . '}';
        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function withs(array $args): RouteBuilder
    {
        if (is_array($args)) {
            foreach ($args as $key => $value) {
                $this->with($key, $value);
            }
        }
        return $this;
    }

    /**
     * Retourne l'uri d'une route nommée
     *
     * @param string $name
     * @return array
     */
    public function getNamedRoute(string $name): string
    {
        return $this->routeGetName[$name];
    }

    /**
     * @return string
     */
    private function resolveUri(): string
    {
        return $this->scheme . $this->host . $this->port . $this->matchAliasPatterns($this->uri);
    }

    protected function matchAliasPatterns($route)
    {
        $patterns = $this->getPatterns();
        return str_replace(array_keys($patterns), array_values($patterns), $route);
    }

    /**
     * @return array
     */
    public function getPatterns(): array
    {
        return array_merge($this->aliasPatterns, $this->addPattern);
    }
}
