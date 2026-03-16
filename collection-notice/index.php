<?php
// index.php (LOGIN)
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/app.php";

if (is_logged_in()) {
  redirect("dashboard.php");
}

$error = "";
$APP_BASE = base_url();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? "");
  $password = (string)($_POST['password'] ?? "");

  if ($username === "" || $password === "") {
    $error = "Please enter username and password.";
  } else {
    $stmt = $pdo->prepare("
      SELECT id, username, password_hash, full_name, position, branch_code
      FROM users
      WHERE username = ?
      LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string)$user['password_hash'])) {
      $error = "Invalid username or password.";
    } else {
      $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => (string)$user['username'],
        'full_name' => (string)$user['full_name'],
        'position' => (string)$user['position'],
        'branch_code' => (string)$user['branch_code'],
      ];

      redirect("dashboard.php");
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Collection Notice</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  body {
    background: #f8f9fe;
  }

  .vh-center {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .card {
    border-radius: 18px;
    border: 0;
  }

  .title-pill {
    font-weight: 700;
    font-size: 20px;
    color: #32325d;
  }

  .password-wrapper {
    position: relative;
  }

  .password-wrapper input {
    padding-right: 48px;
  }

  .eye-btn {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    outline: none;
    padding: 0;
    cursor: pointer;
    color: #8898aa;
    transition: all .25s ease;
  }

  .eye-btn:hover {
    color: #5e72e4;
    transform: translateY(-50%) scale(1.1);
  }

  .eye-btn.active {
    color: #5e72e4;
  }

  .eye-btn svg {
    width: 18px;
    height: 18px;
    transition: opacity .2s ease, transform .2s ease;
  }

  .fade-out {
    animation: fadeOut .35s ease forwards;
  }

  @keyframes fadeOut {
    to {
      opacity: 0;
      transform: scale(.97);
    }
  }

  .spinner-wrap {
    display: none;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 12px;
  }

  .btn-primary {
    background: #5e72e4;
    border: none;
    box-shadow: 0 4px 12px rgba(94,114,228,.3);
    transition: all .2s ease;
  }

  .btn-primary:hover {
    background: #4f63d2;
    transform: translateY(-1px);
  }
</style>
</head>

<body>
<div class="vh-center">
  <div class="container" style="max-width:420px;">
    <div class="card shadow" id="loginCard">
      <div class="text-center pt-4">
        <img src="<?= h(url('assets/img/bank_logo.jpg')) ?>" alt="logo" style="height:64px;">
        <div class="mt-3">
          <span class="title-pill">Collection Notice</span>
        </div>
      </div>

      <div class="card-body p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" id="loginForm">
          <div class="form-group">
            <label>Username</label>
            <input class="form-control" name="username" required>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="password-wrapper">
              <input class="form-control" type="password" name="password" id="pw" required>

              <button type="button" id="togglePw" class="eye-btn">
                <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>

                <svg id="eyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="display:none;">
                  <path d="M1 1l22 22"></path>
                  <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a20.3 20.3 0 0 1 5.06-6.94"></path>
                </svg>
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-block">Login</button>

          <div class="spinner-wrap" id="spinnerWrap">
            <div class="spinner-border text-primary" role="status"></div>
            <small class="text-muted">Signing you in…</small>
          </div>

          <hr>
          <small class="text-muted">
            © Agribusiness Banking Corporation - A Rural Bank
          </small>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const APP_BASE = <?= json_encode($APP_BASE) ?>;

  const pw = document.getElementById("pw");
  const togglePw = document.getElementById("togglePw");
  const eyeOpen = document.getElementById("eyeOpen");
  const eyeClosed = document.getElementById("eyeClosed");

  togglePw.addEventListener("click", () => {
    const visible = pw.type === "password";
    pw.type = visible ? "text" : "password";

    eyeOpen.style.display = visible ? "none" : "block";
    eyeClosed.style.display = visible ? "block" : "none";

    togglePw.classList.toggle("active", visible);
  });

  document.getElementById("loginForm").addEventListener("submit", ()=>{
    const card = document.getElementById("loginCard");
    const spinnerWrap = document.getElementById("spinnerWrap");
    spinnerWrap.style.display = "flex";
    card.classList.add("fade-out");
  });
</script>

</body>
</html>
