<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function add($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch($method, $uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/waste2worth';
        $appPath = parse_url($baseUrl, PHP_URL_PATH);
        if ($appPath && strpos($uri, $appPath) === 0) {
            $uri = substr($uri, strlen($appPath));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                [$controllerName, $methodName] = explode('@', $route['handler']);
                $controllerClass = "App\\Controllers\\$controllerName";
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $methodName)) {
                        $controller->$methodName();
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
