<?php

namespace App;

class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    // -------------------------------------------------------------------------
    // Route registration
    // -------------------------------------------------------------------------

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    // -------------------------------------------------------------------------
    // Dispatch
    // -------------------------------------------------------------------------

    /**
     * Match the current request against registered routes and call the handler.
     * Named segments like {id} are extracted and passed as an associative array.
     */
    public function dispatch(string $method, string $uri): void
    {
        // Strip query string and trailing slash (except bare "/")
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $params = $this->match($route['pattern'], $uri);
            if ($params !== null) {
                ($route['handler'])($params);
                return;
            }
        }

        $this->notFound();
    }

    /**
     * Convert a pattern like /recipes/{id}/edit into a regex,
     * match against $uri, and return named captures — or null on no match.
     *
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $uri): ?array
    {
        // Escape slashes, then replace {name} with named capture groups
        $regex = preg_replace('/\{([a-zA-Z_]+)}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        // Keep only named (string-keyed) captures
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 — Page not found</h1>';
    }
}
