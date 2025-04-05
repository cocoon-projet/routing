<?php
declare(strict_types=1);

namespace Routing\Integration;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

class PostController
{
    public function show(): ResponseInterface
    {
        return new HtmlResponse('Post Show');
    }

    public function index(): ResponseInterface
    {
        return new HtmlResponse('Post Index');
    }

    public function add(): ResponseInterface
    {
        return new HtmlResponse('Post Add');
    }

    public function create(): ResponseInterface
    {
        return new HtmlResponse('Post Create');
    }

    public function edit(): ResponseInterface
    {
        return new HtmlResponse('Post Edit');
    }

    public function update(): ResponseInterface
    {
        return new HtmlResponse('Post Update');
    }

    public function delete(): ResponseInterface
    {
        return new HtmlResponse('Post Delete');
    }
} 