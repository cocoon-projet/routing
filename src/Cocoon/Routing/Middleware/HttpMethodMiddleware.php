<?php

declare(strict_types=1);

namespace Cocoon\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware pour la gestion des méthodes HTTP
 * Permet de gérer les méthodes HTTP alternatives via le champ _METHOD
 */
final class HttpMethodMiddleware implements MiddlewareInterface
{
    /**
     * Liste des méthodes HTTP autorisées pour l'override
     * @var array<string>
     */
    private const ALLOWED_METHODS = ['PUT', 'PATCH', 'DELETE', 'HEAD'];

    /**
     * Traite la requête et gère le override de la méthode HTTP
     */
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $handler->handle(
            $this->handleMethodOverride($request)
        );
    }

    /**
     * Gère le override de la méthode HTTP si nécessaire
     */
    private function handleMethodOverride(ServerRequestInterface $request): ServerRequestInterface
    {
        $parsedBody = $request->getParsedBody();
        $method = $parsedBody['_METHOD'] ?? null;

        if ($method === null) {
            return $request;
        }

        return match(true) {
            in_array($method, self::ALLOWED_METHODS, true) => $request->withMethod($method),
            default => $request
        };
    }
}
