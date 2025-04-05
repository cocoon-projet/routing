<?php

declare(strict_types=1);

namespace Cocoon\Routing;

use Cocoon\Dependency\DI;
use FastRoute\Dispatcher as BaseDispatcher;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Gère le dispatch des routes vers leurs gestionnaires
 *
 * Cette classe est responsable de la résolution et de l'exécution des gestionnaires de routes.
 * Elle supporte les gestionnaires sous forme de chaînes (controller@action), de tableaux ou de callables.
 *
 * @package Cocoon\Routing
 */
class RouteDispatcher
{
    /**
     * Informations de la route à dispatcher
     *
     * @var array{0: int, 1: array<string>|callable|string|null, 2: array<string, string>|null}
     */
    private array $routeInfo;

    /**
     * Constructeur
     *
     * @param array{0: int, 1: array<string>|callable|string|null, 2: array<string, string>|null} $routeInfo
     */
    public function __construct(array $routeInfo)
    {
        $this->routeInfo = $routeInfo;
    }

    /**
     * Dispatch la route vers son gestionnaire
     *
     * @return ResponseInterface
     * @throws RuntimeException Si le contrôleur n'existe pas
     */
    public function dispatch(): ResponseInterface
    {

        if (!is_array($this->routeInfo) || count($this->routeInfo) < 3) {
            return new HtmlResponse('ERROR 404', 404);
        }

        [$status, $handler, $vars] = $this->routeInfo;

        if ($status === BaseDispatcher::NOT_FOUND) {
            return new HtmlResponse('ERROR 404', 404);
        }

        if ($status === BaseDispatcher::METHOD_NOT_ALLOWED) {
            return new HtmlResponse('ERROR 405', 405);
        }
        if ($status === BaseDispatcher::FOUND) {
            $routeVars = $vars ?? [];

            if (is_string($handler)) {
                if (str_contains($handler, '@')) {
                    [$controller, $method] = explode('@', $handler);
                    return $this->resolveHandler([$controller, $method], $routeVars);
                } else {
                    return new HtmlResponse($handler);
                }
            }

            if (is_array($handler)) {
                return $this->resolveHandler($handler, $routeVars);
            }

            if (is_callable($handler)) {
                $response = $handler($routeVars);
                if (!$response instanceof ResponseInterface) {
                    return new HtmlResponse((string) $response);
                }
                return $response;
            }
        }

        throw new RuntimeException('Invalid handler type');
    }

    /**
     * Résout un gestionnaire de type contrôleur
     *
     * @param array<string> $handler Tableau [controller, action]
     * @param array<string, string> $vars Variables de route
     * @return ResponseInterface
     * @throws RuntimeException Si le contrôleur n'existe pas ou retourne un type invalide
     */
    private function resolveHandler(array $handler, array $vars): ResponseInterface
    {
        $controller = $handler[0] ?? null;
        $action = $handler[1] ?? 'index';
        //dumpe($controller, $action);
        if ($controller === null) {
            throw new RuntimeException('Controller not specified in handler');
        }

        if (!class_exists($controller)) {
            throw new RuntimeException("Controller '{$controller}' does not exist");
        }

        $response = DI::make($controller, $action, $vars);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        if (is_string($response)) {
            return new HtmlResponse($response);
        }

        throw new RuntimeException('Controller returned invalid response type');
    }
}
