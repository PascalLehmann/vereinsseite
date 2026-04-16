<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$canTermineDelete = !empty($perms['admin']) || !empty($perms['termine_delete']);

if (!$canTermineDelete) {
    die("Zugriff verweigert.");
}
require_once __DIR__ . '/../../../db.php';
$id = $_GET['id'] ?? 0;
if ($id) {
    $pdo->prepare("UPDATE termine SET is_deleted = 1 WHERE id = ?")->execute([$id]);
}
header("Location: uebersicht.php");
exit;
