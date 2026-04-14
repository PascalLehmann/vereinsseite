<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
if (empty($_SESSION['permissions']['admin']) && !in_array('admin', $_SESSION['roles'] ?? [])) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;

if ($id && $id != $_SESSION['user_id']) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    } catch (Exception $e) {
        die("Fehler beim Löschen.");
    }
}
header("Location: uebersicht.php");
exit;