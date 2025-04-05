<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Controllers;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class UserController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('User Controller Index');
    }

    public function show(int $id): ResponseInterface
    {
        return new HtmlResponse("User Controller Show: {$id}");
    }

    public function store(): ResponseInterface
    {
        return new HtmlResponse('User Controller Store');
    }

    public function update(int $id): ResponseInterface
    {
        return new HtmlResponse("User Controller Update: {$id}");
    }

    public function destroy(int $id): ResponseInterface
    {
        return new HtmlResponse("User Controller Destroy: {$id}");
    }

    public function showPost(int $id, ?int $postId = null): ResponseInterface
    {
        return new HtmlResponse("User Controller Show Post: {$id}" . ($postId ? " Post: {$postId}" : ''));
    }
} 