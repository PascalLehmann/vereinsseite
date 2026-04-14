<?php
session_start();

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM gegner WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: uebersicht.php?deleted=1");
exit;