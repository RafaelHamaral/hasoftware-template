<?php
/**
 * Roteador simples: associa método HTTP + path a [Controller, método].
 */

class Router
{
    /** @var array<string, array<string, array>> */
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $action;
    }

    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $action;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '' : $path;
    }

    public function dispatch(string $requestUri, string $method): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === null || $path === '') {
            $path = '/';
        }
        $path = trim($path, '/');
        if ($path === '' || $path === 'index.php') {
            $path = '';
        }

        $method = strtoupper($method);
        $routes = $this->routes[$method] ?? [];
        $action = $routes[$path] ?? $routes['/' . $path] ?? null;

        if ($action === null) {
            http_response_code(404);
            echo '404 – Página não encontrada';
            return;
        }

        [$controllerClass, $methodName] = $action;
        $controller = new $controllerClass();
        $controller->$methodName();
    }
}
