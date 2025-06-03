<?php

namespace Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, $callback): Router
    {
        $this->routes['GET'][$path] = $callback;
        return $this;
    }

    public function post(string $path, $callback): Router
    {
        $this->routes['POST'][$path] = $callback;
        return $this;
    }

    public function put(string $path, $callback): Router
    {
        $this->routes['PUT'][$path] = $callback;
        return $this;
    }

    public function delete(string $path, $callback): Router
    {
        $this->routes['DELETE'][$path] = $callback;
        return $this;
    }

    public function middleware(array $middlewares): Router
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function resolve()
    {
        $path = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove query string from path
        $path = parse_url($path, PHP_URL_PATH);

        $callback = $this->routes[$method][$path] ?? false;

        if (!$callback) {
            http_response_code(404);
            echo "404 - Page Not Found";
            return;
        }

        // Execute middlewares
        foreach ($this->middlewares as $middleware) {
            $middlewareInstance = new $middleware();
            $middlewareInstance->handle();
        }

        // Execute callback
        if (is_string($callback)) {
            $this->executeControllerAction($callback);
        } else {
            call_user_func($callback);
        }

        // Reset middlewares for next route
        $this->middlewares = [];
    }

    private function executeControllerAction(string $callback)
    {
        [$controller, $action] = explode('@', $callback);
        $controller = "App\\Controllers\\$controller";
        
        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            }
        }
    }
}