<?php

declare(strict_types=1);

namespace Cocoon\Routing\Facade;

use Cocoon\Routing\Router;

/**
 * Facade pour le routeur
 *
 * Cette classe fournit une interface statique simplifiée pour accéder aux fonctionnalités du routeur.
 * Elle implémente le pattern Facade pour masquer la complexité du système de routage.
 *
 * @package Cocoon\Routing\Facade
 */
class Route
{
    /**
     * Instance du routeur
     *
     * @var Router|null
     */
    private static ?Router $instance = null;

    /**
     * Empêche l'instanciation de la classe
     */
    private function __construct()
    {
    }

    /**
     * Empêche le clonage de l'instance
     */
    private function __clone()
    {
    }

    /**
     * Empêche la désérialisation de l'instance
     */
    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize facade');
    }

    /**
     * Gère les appels de méthodes statiques
     *
     * @param string $name Nom de la méthode
     * @param array<mixed> $arguments Arguments de la méthode
     * @return mixed
     * @throws \RuntimeException Si la méthode n'existe pas dans le routeur
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (self::$instance === null) {
            self::$instance = Router::start();
        }

        if (!method_exists(self::$instance, $name)) {
            throw new \RuntimeException("Method '{$name}' does not exist in Router class");
        }

        return self::$instance->$name(...$arguments);
    }

    /**
     * Récupère l'instance du routeur
     *
     * @return Router
     */
    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = Router::start();
        }

        return self::$instance;
    }
}
