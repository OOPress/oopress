<?php
namespace OOPress\Core;

class Response
{
    private int $status = 200;

    public function status(int $code): self
    {
        $this->status = $code;
        http_response_code($code);
        return $this;
    }

    public function text(string $content): void
    {
        header("Content-Type: text/plain");
        echo $content;
    }

    public function html(string $content): void
    {
        header("Content-Type: text/html");
        echo $content;
    }

    public function json(array $data): void
    {
        header("Content-Type: application/json");
        echo json_encode($data);
    }
}
