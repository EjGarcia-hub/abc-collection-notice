<?php
require_once __DIR__ . "/app.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }
}

if (!function_exists('require_login')) {
    function require_login(): void {
        if (!is_logged_in()) {
            redirect("index.php");
        }
    }
}
