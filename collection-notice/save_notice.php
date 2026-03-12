<?php
// save_notice.php
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/auth.php";
require_login();

function fail_json($msg){
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'message' => $msg]);
  exit;
}
function fail_redirect($msg){
  $_SESSION['flash_error'] = $msg;
  header("Location: dashboard.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: dashboard.php");
  exit;
}

$action_type = $_POST['action_type'] ?? 'save_only'; // save_only

$account_no  = trim($_POST['account_no'] ?? "");
$salutation  = trim($_POST['salutation'] ?? "");
$first_name  = trim($_POST['first_name'] ?? "");
$middle_name = trim($_POST['middle_name'] ?? "");
$last_name   = trim($_POST['last_name'] ?? "");
$address     = trim($_POST['address'] ?? "");
$loan_type   = trim($_POST['loan_type'] ?? "");
$notice_no   = trim($_POST['notice_no'] ?? "");

// amort arrays (NOT SAVED; only for validation)
$due_dates = $_POST['due_date'] ?? [];
$amorts    = $_POST['amort'] ?? [];
$penalties = $_POST['penalty'] ?? [];

// validate
$err = null;
if ($account_no === "") $err = "Save failed: Account No. is required.";
if ($first_name === "" || $last_name === "" || $address === "" || $loan_type === "" || $notice_no === "" || $salutation === "") {
  $err = "Save failed: Please complete required client details.";
}
if (!is_array($due_dates) || count($due_dates) < 1) $err = "No amortization rows found. Please add at least one row.";
if (!$err) {
  for ($i=0; $i<count($due_dates); $i++){
    $dd = trim((string)$due_dates[$i]);
    $a  = (float)($amorts[$i] ?? 0);
    $p  = (float)($penalties[$i] ?? 0);
    if ($dd === "") { $err = "Save failed: Please fill the Due Date for all amortization rows."; break; }
    if ($a < 0 || $p < 0) { $err = "Save failed: Negative values are not allowed."; break; }
  }
}

if ($err) {
  // ajax expects json
  if ($action_type === 'save_only') fail_json($err);
  fail_redirect($err);
}

try {
  // ✅ SAVE ONLY CLIENT DETAILS (for borrower search)
  $stmt = $pdo->prepare("SELECT id FROM clients WHERE account_no = ? LIMIT 1");
  $stmt->execute([$account_no]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    $upd = $pdo->prepare("
      UPDATE clients
      SET salutation=?, first_name=?, middle_name=?, last_name=?, address=?, loan_type=?
      WHERE account_no=?
    ");
    $upd->execute([$salutation, $first_name, $middle_name, $last_name, $address, $loan_type, $account_no]);
    $client_id = (int)$existing['id'];
  } else {
    $ins = $pdo->prepare("
      INSERT INTO clients (account_no, salutation, first_name, middle_name, last_name, address, loan_type)
      VALUES (?,?,?,?,?,?,?)
    ");
    $ins->execute([$account_no, $salutation, $first_name, $middle_name, $last_name, $address, $loan_type]);
    $client_id = (int)$pdo->lastInsertId();
  }

  // ✅ respond for AJAX
  if ($action_type === 'save_only') {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'client_id' => $client_id]);
    exit;
  }

  header("Location: dashboard.php?saved=1");
  exit;

} catch (Throwable $e) {
  if ($action_type === 'save_only') {
    fail_json("Save failed: " . $e->getMessage());
  }
  fail_redirect("Save failed: " . $e->getMessage());
}