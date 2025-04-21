<?php
declare(strict_types=1);

namespace Cocoon\Routing;

/**
 * Enregistre une route pour l'application
 *
 * Cette classe permet de construire et configurer une route avec ses paramètres,
 * ses patterns et ses contraintes.
 *
 * @package Cocoon\Routing
 */
class RouteBuilder
{
    /** @var array<string>|string Méthode(s) HTTP */
    private array|string $httpMethod;

    /** @var string URI de la route */
    private string $uri;

    /** @var mixed Gestionnaire de la route (array{0: class-string, 1: string}|callable|string) */
    private mixed $handler;

    /** @var array<string, string> Routes nommées */
    private array $routeGetName = [];

    /** @var string Nom de la route */
    private string $name = '';

    /** @var string Hôte de la route */
    private string $host = '';

    /** @var string Schéma de la route */
    private string $scheme = '';

    /** @var string Port de la route */
    private string $port = '';

    /** @var array<string, string> Patterns d'alias par défaut */
    protected array $aliasPatterns = [
        ':num' => ':[0-9]+',
        ':str' => ':[a-zA-Z]+',
        ':alnum' => ':[a-zA-Z0-9]+',
        ':slug' => ':[a-z0-9\-]+',
        '{id}' => '{id:[0-9]+}',
        '{slug}' => '{slug:[a-z0-9\-]+}'
    ];

    /** @var array<string, string> Patterns additionnels */
    protected array $addPattern = [];

    /**
     * Constructeur
     *
     * @param array<string>|string $httpMethod Méthode(s) HTTP
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string}|callable|string $handler Gestionnaire de la route
     */
    public function __construct(array|string $httpMethod, string $uri, array|callable|string $handler)
    {
        $this->httpMethod = $httpMethod;
        $this->uri = $uri;
        $this->handler = $handler;
    }

    /**
     * Définit un nom pour la route
     *
     * @param string $routeName Nom de la route
     * @return self
     */
    public function name(string $routeName = ''): self
    {
        $this->name = $routeName;
        if (!empty($this->name)) {
            $this->routeGetName[$this->name] = $this->resolveUri();
        }
        return $this;
    }

    /**
     * Retourne le nom de la route
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retourne les paramètres de la route
     *
     * @return array{httpMethod: array<string>|string, route: string,
     * handler: array{0: class-string, 1: string}|callable|string, name: string}
     */
    public function route(): array
    {
        return [
            'httpMethod' => $this->httpMethod,
            'route' => $this->resolveUri(),
            'handler' => $this->handler,
            'name' => $this->name
        ];
    }

    /**
     * Définit l'hôte de la route
     *
     * @param string $host Nom d'hôte
     * @return self
     */
    public function host(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Définit le schéma de la route
     *
     * @param string $scheme Schéma (http, https, etc.)
     * @return self
     */
    public function scheme(string $scheme): self
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Définit le port de la route
     *
     * @param int $port Numéro de port
     * @return self
     */
    public function port(int $port): self
    {
        $this->port = (string) $port;
        return $this;
    }

    /**
     * Ajoute un pattern personnalisé pour un paramètre
     *
     * @param string $arg Nom du paramètre
     * @param string $pattern Pattern de validation
     * @return self
     */
    public function with(string $arg, string $pattern): self
    {
        $this->addPattern['{' . $arg . '}'] = '{' . $arg . ':' . $pattern . '}';
        return $this;
    }

    /**
     * Ajoute plusieurs patterns personnalisés
     *
     * @param array<string, string> $args Tableau de patterns
     * @return self
     */
    public function withs(array $args): self
    {
        foreach ($args as $key => $value) {
            $this->with($key, $value);
        }
        return $this;
    }

    /**
     * Retourne l'URI d'une route nommée
     *
     * @param string $name Nom de la route
     * @return string
     * @throws \InvalidArgumentException Si la route n'existe pas
     */
    public function getNamedRoute(string $name): string
    {
        if (!isset($this->routeGetName[$name])) {
            throw new \InvalidArgumentException("Route named '{$name}' does not exist.");
        }
        return $this->routeGetName[$name];
    }

    /**
     * Résout l'URI finale de la route
     *
     * @return string
     */
    private function resolveUri(): string
    {
        $uri = '';
        
        if (!empty($this->scheme)) {
            $uri .= $this->scheme . '://';
        }
        
        if (!empty($this->host)) {
            $uri .= $this->host;
        }
        
        if (!empty($this->port)) {
            $uri .= ':' . $this->port;
        }
        
        $uri .= $this->matchAliasPatterns($this->uri);
        
        return $uri;
    }

    /**
     * Applique les patterns d'alias à l'URI
     *
     * @param string $route URI de la route
     * @return string
     */
    protected function matchAliasPatterns(string $route): string
    {
        $patterns = $this->getPatterns();
        return str_replace(array_keys($patterns), array_values($patterns), $route);
    }

    /**
     * Retourne tous les patterns disponibles
     *
     * @return array<string, string>
     */
    public function getPatterns(): array
    {
        return array_merge($this->aliasPatterns, $this->addPattern);
    }
}
