<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];

    header("Location: /pages/admin/dashboard.php");
    exit;

} else {
    header("Location: /pages/admin/login.php?error=1");
    exit;
}
