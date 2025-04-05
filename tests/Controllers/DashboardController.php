<?php

declare(strict_types=1);

namespace Cocoon\Routing\Tests\Controllers;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class DashboardController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('Dashboard Controller Index');
    }

    public function show(int $id): ResponseInterface
    {
        return new HtmlResponse("Dashboard Controller Show: {$id}");
    }
} 