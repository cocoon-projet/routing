<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Controllers;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class TestController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('Test Controller Index');
    }

    public function show(int $id): ResponseInterface
    {
        return new HtmlResponse("Test Controller Show: {$id}");
    }

    public function store(): ResponseInterface
    {
        return new HtmlResponse('Test Controller Store');
    }

    public function update(int $id): ResponseInterface
    {
        return new HtmlResponse("Test Controller Update: {$id}");
    }

    public function destroy(int $id): ResponseInterface
    {
        return new HtmlResponse("Test Controller Destroy: {$id}");
    }

    public function handle(): ResponseInterface
    {
        return new HtmlResponse('Test Controller Handle');
    }
} 