<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id']) || !in_array('admin', $_SESSION['rollen'] ?? [])) {
    die("Kein Zugriff.");
}

$id = $_GET['id'] ?? 0;

// Löschen (inkl. Zuordnungen)
$stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
$stmt->execute([$id]);

header("Location: rollen.php");
exit;
