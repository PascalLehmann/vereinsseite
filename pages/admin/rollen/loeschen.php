<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin'])) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("Fehler beim Löschen der Rolle: " . $e->getMessage());
    }
}

header("Location: uebersicht.php");
exit;