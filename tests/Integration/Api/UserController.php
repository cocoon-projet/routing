<?php
declare(strict_types=1);

namespace Routing\Integration\Api;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('API Users');
    }

    public function store(): ResponseInterface
    {
        return new HtmlResponse('API User Store');
    }

    public function show(): ResponseInterface
    {
        return new HtmlResponse('API User Show');
    }

    public function update(): ResponseInterface
    {
        return new HtmlResponse('API User Update');
    }

    public function delete(): ResponseInterface
    {
        return new HtmlResponse('API User Delete');
    }
} 