<?php
require_once __DIR__ . "/config/app.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$_SESSION = [];
session_unset();
session_destroy();

header("Location: " . rtrim(base_url(), '/') . "/");
exit;
