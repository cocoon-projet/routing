<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Controllers;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class PostController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('Post Controller Index');
    }

    public function show(int $id): ResponseInterface
    {
        return new HtmlResponse("Post Controller Show: {$id}");
    }

    public function store(): ResponseInterface
    {
        return new HtmlResponse('Post Controller Store');
    }

    public function update(int $id): ResponseInterface
    {
        return new HtmlResponse("Post Controller Update: {$id}");
    }

    public function destroy(int $id): ResponseInterface
    {
        return new HtmlResponse("Post Controller Destroy: {$id}");
    }

    public function edit(int $id): ResponseInterface
    {
        return new HtmlResponse("Post Controller Edit: {$id}");
    }

    public function create(): ResponseInterface
    {
        return new HtmlResponse('Post Controller Create');
    }
} 