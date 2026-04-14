<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin']) && empty($perms['termine'])) {
    die("Zugriff verweigert.");
}
require_once __DIR__ . '/../../../db.php';
$id = $_GET['id'] ?? 0;
if ($id) {
    $pdo->prepare("UPDATE termine SET is_deleted = 1 WHERE id = ?")->execute([$id]);
}
header("Location: uebersicht.php");
exit;
