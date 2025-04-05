<?php
declare(strict_types=1);

namespace Cocoon\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Cocoon\Routing\RouteCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Gère les routes de l'application.
 *
 * Cette classe implémente le pattern Singleton pour gérer les routes de l'application.
 * Elle permet de définir et dispatcher les routes HTTP de manière flexible et performante.
 *
 * @package Cocoon\Routing
 */
class Router
{
    /** @var Router|null Instance unique de la classe */
    private static ?Router $instance = null;

    /** @var RouteCollection Collection des routes */
    private RouteCollection $routeCollection;

    /** @var string Chemin du cache des routes */
    private string $cachePath = '';

    /**
     * Routes nommées
     *
     * @var array<string, array{route: string, handler: array<string>|callable|string}>
     */
    private array $namedRoutes = [];

    /** @var Dispatcher|null Dispatcher FastRoute */
    protected ?Dispatcher $dispatcher = null;

    /** @var string Préfixe par défaut pour les routes */
    protected string $defaultPrefix = '';

    /** @var bool Configuration du cache des routes */
    protected bool $routeCache = false;

    /** @var string URI avec préfixe */
    protected string $withUri = '';

    /** @var array<string> Méthodes HTTP supportées */
    protected array $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];

    /** @var string URL de base */
    private string $rootBaseUrl = '';

    /** @var string Chemin de base pour toutes les routes */
    private string $basePath = '';

    /** @var array<array{method: string, uri: string, route: string}> Routes ressources par défaut */
    protected array $ressources = [
        ['method' => 'GET', 'uri' => '', 'route' => '@index'],
        ['method' => 'GET', 'uri' => '/add', 'route' => '@add'],
        ['method' => 'POST', 'uri' => '/create', 'route' => '@create'],
        ['method' => 'GET', 'uri' => '/{id}', 'route' => '@show'],
        ['method' => 'GET', 'uri' => '/{id}/edit', 'route' => '@edit'],
        ['method' => 'PUT', 'uri' => '/{id}', 'route' => '@update'],
        ['method' => 'DELETE', 'uri' => '/{id}', 'route' => '@delete']
    ];

    /**
     * Constructeur
     *
     */
    private function __construct()
    {
        $this->rootBaseUrl = trim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''), 2), DIRECTORY_SEPARATOR);
        $this->routeCollection = new RouteCollection();
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
        throw new RuntimeException('Cannot unserialize singleton');
    }

    /**
     * Récupère l'instance unique de la classe
     *
     * @return Router
     */
    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Alias de getInstance pour une utilisation plus fluide
     *
     * @return Router
     */
    public static function start(): Router
    {
        return static::getInstance();
    }

    /**
     * Ajoute une route GET
     *
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function get(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    /**
     * Ajoute une route POST
     *
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function post(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    /**
     * Ajoute une route PUT
     *
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function put(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    /**
     * Ajoute une route DELETE
     *
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function delete(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    /**
     * Ajoute une route PATCH
     *
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function patch(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute('PATCH', $uri, $handler);
    }

    /**
     * Ajoute une route HEAD
     *
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function head(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute('HEAD', $uri, $handler);
    }

    /**
     * Ajoute une route pour toutes les méthodes HTTP
     *
     * @param string $uri URI de la route
     * @param callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function any(string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute($this->methods, $uri, $handler);
    }

    /**
     * Ajoute une route avec les méthodes HTTP spécifiées
     *
     * @param array<string> $httpMethods Méthodes HTTP
     * @param string $uri URI de la route
     * @param callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    public function match(array $httpMethods, string $uri, array|callable|string $handler): RouteBuilder
    {
        return $this->addRoute($httpMethods, $uri, $handler);
    }

    /**
     * Ajoute une route avec le chemin de base
     *
     * @param array|string $httpMethod Méthode HTTP
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     * @return RouteBuilder
     */
    private function addRoute(array|string $httpMethod, string $uri, array|callable|string $handler): RouteBuilder
    {
        if (!empty($this->getBasePath()) && $this->getBasePath() !== '/') {
            $uri = '/' . $this->getBasePath() . '/' . ltrim($uri, '/');
        } else {
            $uri = $this->getBasePath() . '/' . ltrim($uri, '/');
        }
        return $this->routeCollection->add($httpMethod, $uri, $handler);
    }

    /**
     * Ajoute des routes RESTful pour une ressource
     *
     * @param string $name Nom de la ressource
     * @param string $controller Contrôleur
     * @return RouteCollection
     */
    public function resource(string $name, string $controller): RouteCollection
    {
        foreach ($this->ressources as $value) {
            $this->addRoute($value['method'], $name . $value['uri'], $controller . $value['route']);
        }
        return $this->routeCollection;
    }

    /**
     * Groupe des routes avec un préfixe commun
     *
     * @param string $prefix Préfixe des routes
     * @param callable $callback Fonction contenant les routes du groupe
     * @return void
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->defaultPrefix;
        $this->defaultPrefix = $previousPrefix . '/' . trim($prefix, '/');

        $callback($this);

        $this->defaultPrefix = $previousPrefix;
    }

    /**
     * Vérifie si une route nommée existe
     *
     * @param string $namedRoute Nom de la route
     * @return bool
     */
    public function has(string $namedRoute): bool
    {
        return isset($this->namedRoutes[$namedRoute]);
    }

    private function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * Récupère l'URI d'une route nommée
     *
     * @param string $namedRoute Nom de la route
     * @return string
     * @throws InvalidArgumentException Si la route n'existe pas
     */
    public function name($namedRoute): string
    {
        if (array_key_exists($namedRoute, $this->getNamedRoutes())) {
            return $this->getNamedRoutes()[$namedRoute];
        }
    }

    /**
     * Initialise les routes et les routes nommées
     *
     * @return array<array{httpMethod: array<string>|string, route: string, handler: callable|string}>
     */
    protected function initRoutes(): array
    {
        $routes = [];

        foreach ($this->routeCollection->collection() as $built) {
            $route = $built->route();
            $routes[] = $route;
            
            $name = $built->getName();
            if (!empty($name)) {
                $this->namedRoutes[$name] = $built->getNamedRoute($name);
            }
        }
        return $routes;
    }

    /**
     * Récupère toutes les routes
     *
     * @return array<array{httpMethod: array<string>|string, route: string, handler: callable|string}>
     */
    public function getRoutes(): array
    {
        return $this->initRoutes();
    }

    /**
     * Analyse la requête pour trouver la route correspondante
     *
     * @param ServerRequestInterface $request Requête HTTP
     * @return array{0: int, 1: array<string, string>|null, 2: callable|string|null}
     */
    public function make(ServerRequestInterface $request): array
    {

        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();

        // Supprime le chemin de base de l'URI si présent
        if (!empty($this->rootBaseUrl)) {
            $uri = str_replace($this->rootBaseUrl, '', $uri);
        }

        if ($this->dispatcher === null) {
            $this->dispatcher = $this->routeCollector();
        }
        return $this->dispatcher->dispatch($httpMethod, $uri);
    }

    /**
     * Configure le cache des routes
     *
     * @param bool $config État du cache
     * @param string $path Chemin du fichier de cache
     * @return self
     */
    public function cache(bool $config, string $path): self
    {
        $this->routeCache = $config;
        $this->cachePath = $path;
        return $this;
    }

    /**
     * Définit l'URL de base
     *
     * @param string $routeBaseUrl URL de base
     * @return void
     */
    public function rootBaseUrl(string $routeBaseUrl): void
    {
        $this->rootBaseUrl = $routeBaseUrl;
    }

    /**
     * Définit le chemin de base pour toutes les routes
     *
     * @param string $path Chemin de base
     * @return self
     */
    public function setBasePath(string $path): self
    {
        $this->basePath = trim($path, '/');
        return $this;
    }

    /**
     * Récupère le chemin de base
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Initialise le collecteur de routes FastRoute
     *
     * @return Dispatcher
     */
    protected function routeCollector(): Dispatcher
    {
        $routes = $this->getRoutes();
        //dumpe($routes);
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

    /**
     * Dispatch la requête vers le gestionnaire approprié
     *
     * @param ServerRequestInterface $request Requête HTTP
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->make($request);
        return (new RouteDispatcher($route))->dispatch();
    }

    /**
     * Réinitialise le routeur
     *
     * @return void
     */
    public function clear(): void
    {
        $this->routeCollection->clear();
        $this->namedRoutes = [];
        $this->dispatcher = null;
    }

    /**
     * Génère une URL à partir d'une route nommée
     *
     * @param string $name Nom de la route
     * @param array<string, string> $parameters Paramètres de la route
     * @return string URL générée
     * @throws \InvalidArgumentException Si la route n'existe pas ou si des paramètres sont manquants
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new InvalidArgumentException("Route nommée '{$name}' non trouvée.");
        }
        
        $path = $this->namedRoutes[$name];
        //dump($path);
        // Remplacer les paramètres dans le chemin
        foreach ($parameters as $key => $value) {
            // Supprime le pattern de correspondance s'il existe (ex: {id:[0-9]+} -> {id})
            $path = preg_replace('/\{' . $key . ':[^}]+\}/', '{' . $key . '}', $path);
            $path = str_replace(['{' . $key . '}'], [$value], $path);
        }
        //dumpe($path);
        // Vérifier s'il reste des paramètres obligatoires non remplis
        if (preg_match('/\{([^}]+)\}/', $path)) {
            throw new InvalidArgumentException("Paramètres manquants pour la route '{$name}'");
        }
        //dumpe($path);
        return $path;
    }
}
