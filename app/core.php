<?php
declare(strict_types=1);

session_start();

$config = require __DIR__ . '/config.php';

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(function (string $class): void {
        if (str_starts_with($class, 'App\\')) {
            $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (is_file($path)) require $path;
        }
    });
}

function cfg(string $path, mixed $default = null): mixed
{
    global $config;
    $parts = explode('.', $path);
    $val = $config;
    foreach ($parts as $p) {
        if (!is_array($val) || !array_key_exists($p, $val)) return $default;
        $val = $val[$p];
    }
    return $val;
}

function site_path(): string
{
    $site_path = (string)cfg('app.site_path', '');
    return rtrim($site_path, '/');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || !$token) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function is_ajax(): bool
{
    return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function escape_html(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Простейшая “шаблонизация” (include view)
 * render('form', ['siteKey' => '...', 'csrf' => '...'])
 */
function render(string $view, array $data = []): void
{
    $file = __DIR__ . '/Views/' . $view . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("Шаблон не найден: {$view}");
    }
    extract($data, EXTR_SKIP);
    include $file;
}
