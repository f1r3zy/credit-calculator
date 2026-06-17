<?php
namespace App\Core;

class Response
{
    private int $statusCode;
    private mixed $data;
    private array $headers = [];

    public function __construct(int $statusCode = 200, mixed $data = '')
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    public function withHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if (is_array($this->data)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo $this->data;
        }

        exit;
    }

    public static function error(int $code, string $message): self
    {
        return new self($code, ['error' => $message]);
    }
}