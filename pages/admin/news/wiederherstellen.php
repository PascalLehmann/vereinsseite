<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin'])) {
    die("Zugriff verweigert: Nur Admins können News wiederherstellen.");
}
require_once __DIR__ . '/../../../db.php';
$id = $_GET['id'] ?? 0;
if ($id) {
    $pdo->prepare("UPDATE news SET is_deleted = 0 WHERE id = ?")->execute([$id]);
}
header("Location: uebersicht.php");
exit;