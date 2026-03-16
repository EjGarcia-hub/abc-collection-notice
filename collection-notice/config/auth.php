<?php
require_once __DIR__ . "/app.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function is_logged_in(): bool {
  return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function require_login(): void {
  if (!is_logged_in()) {
    redirect("index.php");
  }
}
