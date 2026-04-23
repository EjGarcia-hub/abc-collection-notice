<?php
// save_notice.php
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/app.php";
require_login();

function fail_json($msg){
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'message' => $msg]);
  exit;
}
function fail_redirect($msg){
  $_SESSION['flash_error'] = $msg;
  redirect("dashboard");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect("dashboard");
}

$action_type = $_POST['action_type'] ?? 'save_only';

/* =========================
   TEMPLATE-ALIGNED FIELDS
   ========================= */
$account_no      = trim($_POST['account_no'] ?? "");
$pn_no           = trim($_POST['pn_no'] ?? $account_no);
$salutation      = trim($_POST['salutation'] ?? "");
$first_name      = trim($_POST['first_name'] ?? "");
$middle_name     = trim($_POST['middle_name'] ?? "");
$last_name       = trim($_POST['last_name'] ?? "");
$address         = trim($_POST['address'] ?? "");
$province        = trim($_POST['province'] ?? "");
$loan_type       = trim($_POST['loan_type'] ?? "");
$notice_no       = strtoupper(trim($_POST['notice_no'] ?? ""));
$loan_amount     = trim($_POST['loan_amount'] ?? "");
$past_due_since  = trim($_POST['past_due_since'] ?? "");
$notice_date     = trim($_POST['notice_date'] ?? date('Y-m-d'));

$sign_name       = trim($_POST['sign_name'] ?? "");
$sign_position   = trim($_POST['sign_position'] ?? "");
$branch_name     = trim($_POST['branch_name'] ?? "");
$branch_address  = trim($_POST['branch_address'] ?? "");

/* =========================
   VALIDATION
   ========================= */
$err = null;

if ($account_no === "") $err = "Save failed: Account No. is required.";
if (!$err && $salutation === "") $err = "Save failed: Salutation is required.";
if (!$err && $first_name === "") $err = "Save failed: First Name is required.";
if (!$err && $last_name === "") $err = "Save failed: Last Name is required.";
if (!$err && $address === "") $err = "Save failed: Address is required.";
if (!$err && $province === "") $err = "Save failed: Province is required.";
if (!$err && $loan_type === "") $err = "Save failed: Loan Type is required.";
if (!$err && $notice_no === "") $err = "Save failed: Notice No. is required.";
if (!$err && $pn_no === "") $err = "Save failed: Promissory Note No. is required.";
if (!$err && $loan_amount === "") $err = "Save failed: Loan Amount is required.";
if (!$err && $past_due_since === "") $err = "Save failed: Past Due Since is required.";

if (!$err && !in_array($notice_no, ['FIRST', 'SECOND', 'THIRD'], true)) {
  $err = "Save failed: Invalid Notice No.";
}

if (!$err) {
  $loan_amount_num = str_replace([',', '₱', ' '], '', $loan_amount);
  if (!is_numeric($loan_amount_num)) {
    $err = "Save failed: Loan Amount must be numeric.";
  } else {
    $loan_amount_num = number_format((float)$loan_amount_num, 2, '.', '');
  }
}

if (!$err && strtotime($past_due_since) === false) {
  $err = "Save failed: Invalid Past Due Since date.";
}
if (!$err && strtotime($notice_date) === false) {
  $err = "Save failed: Invalid Notice Date.";
}

if ($err) {
  if ($action_type === 'save_only') fail_json($err);
  fail_redirect($err);
}

try {
  $pdo->beginTransaction();

  /* ==========================================================
     1) SAVE / UPDATE CLIENT RECORD
     Add these columns in clients table if not yet existing:
     - province
     - pn_no
     - loan_amount
     - past_due_since
     ========================================================== */
  $stmt = $pdo->prepare("SELECT id FROM clients WHERE account_no = ? LIMIT 1");
  $stmt->execute([$account_no]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    $upd = $pdo->prepare("
      UPDATE clients
      SET
        salutation = ?,
        first_name = ?,
        middle_name = ?,
        last_name = ?,
        address = ?,
        province = ?,
        loan_type = ?,
        pn_no = ?,
        loan_amount = ?,
        past_due_since = ?
      WHERE account_no = ?
    ");
    $upd->execute([
      $salutation,
      $first_name,
      $middle_name,
      $last_name,
      $address,
      $province,
      $loan_type,
      $pn_no,
      $loan_amount_num,
      $past_due_since,
      $account_no
    ]);
    $client_id = (int)$existing['id'];
  } else {
    $ins = $pdo->prepare("
      INSERT INTO clients (
        account_no, salutation, first_name, middle_name, last_name,
        address, province, loan_type, pn_no, loan_amount, past_due_since
      )
      VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");
    $ins->execute([
      $account_no,
      $salutation,
      $first_name,
      $middle_name,
      $last_name,
      $address,
      $province,
      $loan_type,
      $pn_no,
      $loan_amount_num,
      $past_due_since
    ]);
    $client_id = (int)$pdo->lastInsertId();
  }

  /* ==========================================================
     2) SAVE NOTICE RECORD
     Create this table if not yet existing, e.g.:
     notices(
       id SERIAL PRIMARY KEY,
       client_id INT NOT NULL,
       account_no VARCHAR(100) NOT NULL,
       pn_no VARCHAR(100) NOT NULL,
       notice_no VARCHAR(20) NOT NULL,
       notice_date DATE NOT NULL,
       loan_type VARCHAR(150),
       loan_amount NUMERIC(15,2),
       past_due_since DATE,
       sign_name VARCHAR(150),
       sign_position VARCHAR(150),
       branch_name VARCHAR(150),
       branch_address TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     )
     ========================================================== */
  $noticeIns = $pdo->prepare("
    INSERT INTO notices (
      client_id,
      account_no,
      pn_no,
      notice_no,
      notice_date,
      loan_type,
      loan_amount,
      past_due_since,
      sign_name,
      sign_position,
      branch_name,
      branch_address
    )
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
  ");
  $noticeIns->execute([
    $client_id,
    $account_no,
    $pn_no,
    $notice_no,
    $notice_date,
    $loan_type,
    $loan_amount_num,
    $past_due_since,
    $sign_name,
    $sign_position,
    $branch_name,
    $branch_address
  ]);

  $notice_id = (int)$pdo->lastInsertId();

  $pdo->commit();

  if ($action_type === 'save_only') {
    header('Content-Type: application/json');
    echo json_encode([
      'ok' => true,
      'client_id' => $client_id,
      'notice_id' => $notice_id
    ]);
    exit;
  }

  redirect("dashboard?saved=1");

} catch (Throwable $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  if ($action_type === 'save_only') {
    fail_json("Save failed: " . $e->getMessage());
  }
  fail_redirect("Save failed: " . $e->getMessage());
}
