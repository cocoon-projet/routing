<?php
declare(strict_types=1);

namespace Routing\Integration\Api;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

class PostController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('API Posts');
    }
} 