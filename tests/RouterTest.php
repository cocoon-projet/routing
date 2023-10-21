<?php


namespace Routing;


class RouterTest extends RoutingTest
{
    public $routeName = 'test_name';

    public function routeArray($method, $uri , $handler, $name = ''): array
    {
        return  ['httpMethod' => $method , 'route' => $uri, 'handler' => $handler, 'name' => $name];
    }

    public function testAllRoute()
    {
        $methods = ['GET','POST','PUT','DELETE', 'PATCH', 'HEAD'];
        $uri = ['/','/r_home','/r_add','/r_update','/r_delete','/r_show'];
        $handler = static function () {
            return 'ok';
        };
        $size = count($methods);
        for ($i = 0; $i < $size; $i++) {
            $meth = strtolower($methods[$i]);
            $this->route->$meth($uri[$i], $handler);
            $this->assertEquals($this->routeArray($methods[$i], $uri[$i], $handler), $this->route->getRoutes()[$i]);
        }
    }

    public function testNamedAndPatternRoute()
    {
        $uri = '/n_test/{page}';
        $handler = static function () {
        };
        $namedRoute = $this->route->get($uri, $handler)->name($this->routeName)->withs(['page' => '\d+']);
        $this->assertEquals($namedRoute->getName(), $this->routeName);
        $this->assertIsArray($this->route->getRoutes());
        $this->assertIsArray($namedRoute->getPatterns());
        $this->assertArrayHasKey('{page}', $namedRoute->getPatterns());
        $this->assertTrue($this->route->has($this->routeName));
    }

    public function testRouteWithHostAndSchemeAndPort()
    {
        $uri = '/route';
        $handler = static function () {
        };
        $url = 'https://www.example.com:8080/route';
        $route = $this->route->get($uri, $handler)->scheme('https')->host('www.example.com')->port(8080)->name('test');
        $this->assertEquals($route->getNamedRoute('test'), $url);
    }

    public function testRouteGroup()
    {
        $route = $this->route;
        $uri_one = '/group_test/home';
        $uri_two = '/group_test/add';
        $route->group('/group_test', function() use ($route)
        {
            $handler = static function () {
                return 'home test';
            };
            $route->get('/home', $handler)->name('home');
            $route->get('/add', $handler)->name('add');
        }
    );
        $route->getRoutes();
        $this->assertEquals($route->name('home'), $uri_one);
        $this->assertEquals($route->name('add'), $uri_two);
    }

}