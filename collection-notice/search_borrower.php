<?php
// search_borrower.php (Supabase / Postgres)
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/auth.php";
require_login();

$q = trim($_GET['q'] ?? "");
$accountOnly = (int)($_GET['accountOnly'] ?? 0);

header("Content-Type: application/json; charset=utf-8");

if ($q === "") {
  echo json_encode(["items" => []]);
  exit;
}

$limit = 10;

if ($accountOnly === 1) {
  // ✅ account no only
  $stmt = $pdo->prepare("
    SELECT id, account_no, salutation, first_name, middle_name, last_name, address, loan_type
    FROM clients
    WHERE account_no ILIKE :prefix
    ORDER BY account_no ASC
    LIMIT :lim
  ");
  $stmt->bindValue(':prefix', $q . "%", PDO::PARAM_STR);
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stmt->execute();

} else {
  // ✅ name OR account (case-insensitive)
  // Postgres supports concat_ws() so we can keep your logic.
  $stmt = $pdo->prepare("
    SELECT id, account_no, salutation, first_name, middle_name, last_name, address, loan_type
    FROM clients
    WHERE account_no ILIKE :like
       OR concat_ws(' ', first_name, middle_name, last_name) ILIKE :like
       OR last_name ILIKE :like
    ORDER BY
      CASE WHEN account_no = :exact THEN 0 ELSE 1 END,
      last_name ASC,
      first_name ASC
    LIMIT :lim
  ");
  $like = "%" . $q . "%";
  $stmt->bindValue(':like', $like, PDO::PARAM_STR);
  $stmt->bindValue(':exact', $q, PDO::PARAM_STR);
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stmt->execute();
}

$rows = $stmt->fetchAll();

$items = array_map(function($r){
  $full = trim(($r['first_name'] ?? '') . " " . ($r['middle_name'] ?? '') . " " . ($r['last_name'] ?? ''));
  return [
    "id" => (int)$r["id"],
    "account_no" => $r["account_no"],
    "salutation" => $r["salutation"],
    "first_name" => $r["first_name"],
    "middle_name" => $r["middle_name"],
    "last_name" => $r["last_name"],
    "full_name" => $full,
    "address" => $r["address"],
    "loan_type" => $r["loan_type"],
  ];
}, $rows);

echo json_encode(["items" => $items], JSON_UNESCAPED_UNICODE);