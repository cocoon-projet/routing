<?php
declare(strict_types=1);

namespace Cocoon\Routing;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

/**
 * Classe qui stocke les routes de l'application
 *
 * Cette classe gère la collection de routes de l'application.
 * Elle permet d'ajouter des routes et de les récupérer pour le dispatch.
 *
 * @package Cocoon\Routing
 */
class RouteCollection
{
    /** @var RouteCollector Collecteur de routes */
    private RouteCollector $routeCollector;

    /** @var array<string, mixed> Routes enregistrées */
    private array $routes = [];

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->routeCollector = new RouteCollector(
            new RouteParser(),
            new DataGenerator()
        );
    }

    /**
     * Réinitialise la collection de routes
     *
     * @return void
     */
    public function clear(): void
    {
        $this->routeCollector = new RouteCollector(
            new RouteParser(),
            new DataGenerator()
        );
        $this->routes = [];
    }

    /**
     * Ajoute une nouvelle route à la collection
     *
     * @param array<string>|string $methods Méthodes HTTP acceptées
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $action Gestionnaire de la route
     * @return RouteBuilder
     */
    public function add(array|string $methods, string $uri, array|callable|string $action): RouteBuilder
    {
        $route = new RouteBuilder($methods, $uri, $action);
        $this->routes[$uri] = $route;
        return $route;
    }

    /**
     * Retourne la collection de routes
     *
     * @return array<RouteBuilder>
     */
    public function collection(): array
    {
        return $this->routes;
    }

    /**
     * Vérifie si la collection est vide
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->routes);
    }

    /**
     * Retourne le nombre de routes dans la collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->routes);
    }
}
