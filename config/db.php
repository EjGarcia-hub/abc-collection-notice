<?php
$host = "aws-1-ap-northeast-1.pooler.supabase.com";  // copy exact from Supabase (Session/Transaction pooler)
$port = 6543;                                  // session pooler often uses 5432; transaction pooler often 6543
$db   = "postgres";
$user = "postgres.mhsojrnkdopdonqghian";              // IMPORTANT: postgres.<project_ref>
$pass = "qKUWa5dX12zJjTKy";

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}