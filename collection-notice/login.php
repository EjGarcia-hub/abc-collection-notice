<?php
require "config/db.php";
require "config/auth.php";

if (!empty($_SESSION['user'])) {
  header("Location: dashboard.php");
  exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = (string)($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    $error = "Please enter your username and password.";
  } else {
    $stmt = $pdo->prepare("
      SELECT id, username, password_hash, full_name, position, branch, is_active
      FROM users
      WHERE username = :u
      LIMIT 1
    ");
    $stmt->execute([':u' => $username]);
    $u = $stmt->fetch();

    // ✅ Postgres boolean can be true/false or 't'/'f' depending on fetch mode/driver
    $isActive = false;
    if ($u && array_key_exists('is_active', $u)) {
      $v = $u['is_active'];
      $isActive = ($v === true || $v === 1 || $v === '1' || $v === 't' || $v === 'true');
    }

    if (!$u || !$isActive || !password_verify($password, (string)$u['password_hash'])) {
      $error = "Invalid login.";
    } else {
      $_SESSION['user'] = [
        'id' => (int)$u['id'],
        'username' => (string)$u['username'],
        'full_name' => (string)$u['full_name'],
        'position' => (string)$u['position'],
        'branch' => (string)$u['branch'],
      ];
      header("Location: dashboard.php");
      exit;
    }
  }
}
?>