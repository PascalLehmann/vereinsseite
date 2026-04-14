<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin'])) {
    die("Zugriff verweigert: Nur Admins können endgültig löschen.");
}
require_once __DIR__ . '/../../../db.php';
$id = $_GET['id'] ?? 0;
if ($id) {
    $pdo->prepare("DELETE FROM termine WHERE id = ?")->execute([$id]);
}
header("Location: uebersicht.php");
exit;