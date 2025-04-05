<?php
declare(strict_types=1);

namespace Routing\Unit;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

class TestController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('Test index');
    }

    public function show(): ResponseInterface
    {
        return new HtmlResponse('Test show');
    }

    public function handle(): ResponseInterface
    {
        return new HtmlResponse('Test handle');
    }
} 