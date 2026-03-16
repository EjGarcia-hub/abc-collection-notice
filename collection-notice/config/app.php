<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal =
    stripos($host, 'localhost') !== false ||
    stripos($host, '127.0.0.1') !== false ||
    stripos($host, 'collectionnotice.local') !== false;

define('APP_BASE', $isLocal ? '/collection-notice' : '');

if (!function_exists('base_url')) {
    function base_url(): string {
        return APP_BASE;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $path = ltrim($path, '/');
        return rtrim(APP_BASE, '/') . ($path !== '' ? '/' . $path : '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
