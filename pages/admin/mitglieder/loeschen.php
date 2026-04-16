<?php
session_start();

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin']) && empty($perms['mitglieder_delete'])) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Optional: Altes Bild vom Server löschen
    $stmt = $pdo->prepare("SELECT profilbild FROM mitglieder WHERE id = ?");
    $stmt->execute([$id]);
    $bild = $stmt->fetchColumn();

    if ($bild) {
        $filePath = __DIR__ . '/../../../assets/img/mitglieder/' . $bild;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM mitglieder WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: übersicht.php?deleted=1");
exit;