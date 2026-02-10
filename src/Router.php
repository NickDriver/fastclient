<?php

declare(strict_types=1);

namespace App;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $groupPrefix = '';
    private array $groupMiddlewares = [];

    public function get(string $path, callable|array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): self
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupMiddlewares = array_merge($previousMiddlewares, $middlewares);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;

        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $lastKey = array_key_last($this->routes);
        if ($lastKey !== null) {
            $middlewares = is_array($middleware) ? $middleware : [$middleware];
            $this->routes[$lastKey]['middlewares'] = array_merge(
                $this->routes[$lastKey]['middlewares'],
                $middlewares
            );
        }
        return $this;
    }

    private function addRoute(string $method, string $path, callable|array $handler): self
    {
        $fullPath = $this->groupPrefix . $path;
        $pattern = $this->pathToPattern($fullPath);

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $this->groupMiddlewares,
        ];

        return $this;
    }

    private function pathToPattern(string $path): string
    {
        // Convert route parameters like {id} to regex groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): mixed
    {
        // Handle method override for PUT/DELETE from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Remove query string from URI
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $result = $middleware->handle();
                    if ($result !== true) {
                        return $result;
                    }
                }

                // Call handler
                $handler = $route['handler'];

                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    return $controller->$method(...array_values($params));
                }

                return $handler(...array_values($params));
            }
        }

        // 404 Not Found
        http_response_code(404);
        return view('errors.404');
    }
}
