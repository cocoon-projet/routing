<?php
declare(strict_types=1);

namespace Routing\Integration\Admin;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

class SettingController
{
    public function index(): ResponseInterface
    {
        return new HtmlResponse('Admin Settings');
    }
} 