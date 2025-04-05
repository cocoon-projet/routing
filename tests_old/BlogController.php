<?php


namespace Routing;


class BlogController
{
    public function index()
    {
        return 'test_home';
    }

    public function show($id)
    {
        return 'test_show ' . $id;
    }
}