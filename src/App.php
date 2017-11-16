<?php

namespace App;

use App\Exception\RouterException;

/**
 * Class App
 * @package App
 */
class App
{

    /**
     * @var
     */
    private $url;

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $namedRoutes = [];


    /**
     * App constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }


    /**
     * @param string $path
     * @param $callable
     * @param null $name
     * @return Route
     */
    public function get(string $path, $callable, $name = null)
    {
        return $this->add($path, $callable, $name, 'GET');
    }


    /**
     * @param string $path
     * @param $callable
     * @param null $name
     * @return Route
     */
    public function post(string $path, $callable, $name = null)
    {
        return $this->add($path, $callable, $name, 'POST');
    }


    /**
     * @param string $path
     * @param $callable
     * @param null|string $name
     * @param $method
     * @return Route
     */
    private function add(string $path, $callable, ?string $name, $method)
    {
        $route = new Route($path, $callable);
        $this->routes[$method][] = $route;
        if($name) {
            $this->namedRoutes[$name] = $route;
        }
        return $route;
    }


    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws RouterException
     */
    public function url(string $name, array $params = [])
    {
        if(!isset($this->namedRoutes[$name])) {
            throw new RouterException('No routes name matches');
        }

        return $this->namedRoutes[$name]->getUrl($params);
    }


    /**
     * @return mixed
     * @throws RouterException
     */
    public function run()
    {
        if(!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('Request Method does not exists');
        }

        foreach($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if($route->match($this->url)) {
                return $route->call();
            }
        }
        throw new RouterException('No routes matches');
    }
}