<?php
declare(strict_types=1);
namespace App\Core;

class Request
{
    private string $method;
    private string $path;
    private array $queryParams;
    private array $bodyParams;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->path = $uri ?: '/';
        $this->queryParams = $_GET;
        $this->parseBody();
    }

    private function parseBody(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $this->bodyParams = json_decode($raw, true) ?: [];
        } else {
            $this->bodyParams = $_POST;
        }
    }

    public function getMethod(): string { return $this->method; }
    public function getPath(): string { return $this->path; }

    // *** METODA CARE LIPSEA ***
    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    public function input(string $key, $default = null)
    {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        $headers = getallheaders() ?: [];
        $name = strtolower($name);
        foreach ($headers as $h => $v) {
            if (strtolower($h) === $name) return $v;
        }
        return null;
    }
}