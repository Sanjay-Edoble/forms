<?php

namespace App\Core;

/**
 * Lightweight PHP Router with named parameters and middleware support.
 */
class Router
{
    private array $routes = [];
    private array $middlewareGroups = [];
    private string $prefix = '';
    private array $currentMiddleware = [];

    /**
     * Register a GET route.
     */
    public function get(string $path, array|callable $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, array|callable $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route.
     */
    public function put(string $path, array|callable $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a PATCH route.
     */
    public function patch(string $path, array|callable $handler): self
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $path, array|callable $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Group routes with shared prefix and/or middleware.
     */
    public function group(array $options, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->currentMiddleware;

        $this->prefix .= $options['prefix'] ?? '';
        $this->currentMiddleware = array_merge(
            $this->currentMiddleware,
            $options['middleware'] ?? []
        );

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    /**
     * Add a route.
     */
    private function addRoute(string $method, string $path, array|callable $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'handler'    => $handler,
            'middleware'  => $this->currentMiddleware,
            'pattern'    => $this->buildPattern($fullPath),
        ];
        return $this;
    }

    /**
     * Build a regex pattern from a route path.
     */
    private function buildPattern(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch the current request.
     */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        // Support method override via _method field
        if ($method === 'POST') {
            $override = $request->input('_method');
            if ($override && in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'])) {
                $method = strtoupper($override);
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware chain
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $result = $middleware->handle($request);
                    if ($result === false) {
                        return; // Middleware aborted the request
                    }
                }

                // Call the handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controllerClass, $methodName] = $handler;
                    $controller = new $controllerClass();
                    $controller->$methodName($request, $params);
                } elseif (is_callable($handler)) {
                    $handler($request, $params);
                }
                return;
            }
        }

        // No route matched
        Response::notFound('Page not found');
    }
}
