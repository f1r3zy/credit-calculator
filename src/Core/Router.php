<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable|string $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getPath();

        foreach ($this->routes as $route) {

            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];

                if (is_string($handler) && str_contains($handler, '@')) {

                    [$class, $action] = explode('@', $handler);

                    $controller = new $class();

                    return $controller->$action($request, ...$params);
                }

                if (is_callable($handler)) {
                    return $handler($request, ...$params);
                }

                return Response::error(500, 'Handler invalid');
            }
        }

        return Response::error(404, 'Pagina nu exista');
    }
}