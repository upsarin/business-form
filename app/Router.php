<?php
namespace App;

class Router
{
    public function dispatch(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $base = site_path();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        if ($path === '/' && $method === 'GET') {
            (new \App\Controllers\FormController())->show();
            return;
        }

        if ($path === '/submit' && $method === 'POST') {
            (new \App\Controllers\ApplyController())->submit();
            return;
        }

        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Not Found";
    }
}
