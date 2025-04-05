<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Controllers;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class ApiController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('API Controller Index');
    }

    public function show(int $id): ResponseInterface
    {
        return new HtmlResponse("API Controller Show: {$id}");
    }
} 