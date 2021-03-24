<?php


namespace Cocoon\Routing;

/**
 * Classe qui stocke les routes de l'application
 *
 * Class RouteCollection
 * @package Cocoon\Routing
 */
class RouteCollection
{
    /**
     * Collection de routes
     *
     * @var array
     */
    private $collection = [];

    /**
     * Ajout d'une route
     *
     * @param string|array $methods
     * @param string $uri
     * @param  $action
     * @return RouteBuilder
     */
    public function add($methods, string $uri, $action): RouteBuilder
    {
        $route = new RouteBuilder($methods, $uri, $action);
        $this->collection[] = $route;
        return $route;
    }

    /**
     * retourne une collection de routes
     *
     * @return array
     */
    public function collection(): array
    {
        return $this->collection;
    }
}
