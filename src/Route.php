<?php

namespace App;
use App\Exception\RouterException;

/**
 * Class Route
 * @package App
 */
class Route
{

    /**
     * @var string
     */
    private $path;
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var
     */
    private $matches;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Route constructor.
     * @param string $path
     * @param callable $callable
     */
    public function __construct(string $path, $callable)
    {
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }

    /**
     * @param string $params
     * @param $value
     * @return $this
     */
    public function with(string $params, $value)
    {
        $this->params[$params] = str_replace('(', '(?:', $value);
        return $this;
    }

    /**
     * @param $url
     * @return bool
     */
    public function match($url)
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramsMatch'], $this->path);
        $regex = "#^$path$#i";
        if(!preg_match($regex, $url, $matches)) {
            return false;
        }

        array_shift($matches);
        $this->matches = $matches;
        return true;
    }


    /**
     * @param $match
     * @return string
     */
    public function paramsMatch($match)
    {
        if(isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }

        return '([^/]+)';
    }

    /**
     * @param array $params
     * @return mixed|string
     */
    public function getUrl(array $params = [])
    {
        $path = $this->path;
        foreach($params as $k => $v) {
            $path = str_replace(":$k", $v, $path);
        }

        return $path;
    }

    /**
     * @return mixed
     * @throws RouterException
     */
    public function call()
    {
        if(is_string($this->callable)) {
            $params = explode(':', $this->callable);
            $contrl = "App\\Controllers\\" . $params[0] . "Controller";
            if(!class_exists($contrl)) {
                throw new RouterException('Controller not found');
            }
            $controller = new $contrl();
            return call_user_func_array([$controller, $params[1]], $this->matches);
        }else {
            return call_user_func_array($this->callable, $this->matches);
        }
    }
}