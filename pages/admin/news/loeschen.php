<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['news_delete'])) {
    die("Zugriff verweigert.");
}
require_once __DIR__ . '/../../../db.php';
$id = $_GET['id'] ?? 0;
if ($id) {
    $pdo->prepare("UPDATE news SET is_deleted = 1 WHERE id = ?")->execute([$id]);
}
header("Location: uebersicht.php");
exit;