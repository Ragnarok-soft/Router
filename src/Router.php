<?php

namespace Ragnarok;

namespace Core\Modules\Route;

class Router {

    private RouteStorage $collection;

    public function __construct(RouteStorage $collection)
    {
        $this->collection = $collection;
    }

    public function __destruct()
    {
        $request = new Request();

        foreach ($this->collection as $route) {

            $parseRoute = rtrim($route->getRegex(), '/');
            $pattern = '@^' . preg_quote('/') . $parseRoute . '/?$@i';

            if (!preg_match($pattern, $request->getClearURL(), $matches)) {
                continue;
            }

            if (!$request->isMethod($route->getMethod())) {
                continue;
            }

            $params = [];

            if (preg_match_all('/:(\w+)/', $route->getUrl(), $param_keys)) {
                // grab array with matches
                $param_keys = $param_keys[1];

                if(count($param_keys) !== (count($matches) -1)) {
                    continue;
                }

                // loop trough parameter names, store matching value in $params array
                foreach ($param_keys as $key => $name) {
                    if (isset($matches[$key+1])) {
                        $params[$name] = $matches[$key+1];
                    }
                }

            }

            $params[] = $request;
            $route->setParameters($params);

            return print_r($route->dispatch());

        }

        http_error_404();

    }
}