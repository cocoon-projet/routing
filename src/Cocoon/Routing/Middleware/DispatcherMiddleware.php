<?php

declare(strict_types=1);

namespace Cocoon\Routing\Middleware;

use Cocoon\Dependency\DI;
use Cocoon\Routing\Router;
use FastRoute\Dispatcher as BaseDispatcher;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Middleware de dispatch des routes
 *
 * Ce middleware est responsable du traitement des requêtes HTTP et de leur dispatch
 * vers les gestionnaires appropriés. Il implémente l'interface PSR-15 MiddlewareInterface.
 *
 * @package Cocoon\Routing\Middleware
 */
class DispatcherMiddleware implements MiddlewareInterface
{
    /**
     * Instance du routeur
     *
     * @var Router
     */
    private Router $router;

    /**
     * Constructeur
     *
     * @param Router $router Instance du routeur
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Traite la requête HTTP
     *
     * @param ServerRequestInterface $request Requête HTTP
     * @param RequestHandlerInterface $handler Gestionnaire de requête
     * @return ResponseInterface
     * @throws RuntimeException Si le contrôleur n'existe pas ou retourne un type invalide
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeInfo = $this->router->make($request);
        if (!is_array($routeInfo) || count($routeInfo) < 3) {
            return new HtmlResponse('ERROR 404', 404);
        }

        [$status, $handler, $vars] = $routeInfo;

        if ($status === BaseDispatcher::NOT_FOUND) {
            return new HtmlResponse('ERROR 404', 404);
        }

        if ($status === BaseDispatcher::METHOD_NOT_ALLOWED) {
            return new HtmlResponse('ERROR 405', 405);
        }

        if ($handler === null) {
            throw new RuntimeException('No handler found for route');
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
