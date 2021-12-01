<?php

namespace App\Helpers;

class JsonHandler
{
    public static function request(): array
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    public static function response($data = null, int $statusCode = 200): void
    {
        header_remove();
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}