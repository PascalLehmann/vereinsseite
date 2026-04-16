<?php
session_start();
require_once __DIR__ . '/../../../db.php';

// --- 1. Berechtigungen prüfen ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
$canNewsEdit = !empty($perms['news_edit']);
$canNewsDelete = !empty($perms['news_delete']);
$canNewsDeleteHard = !empty($perms['news_delete_hard']);
if (!$canNewsEdit) {
    die("Zugriff verweigert.");
}

// --- 2. Eingaben validieren ---
$bild_id = filter_input(INPUT_GET, 'bild_id', FILTER_VALIDATE_INT);
$news_id = filter_input(INPUT_GET, 'news_id', FILTER_VALIDATE_INT);
$action = $_GET['action'] ?? '';

if (!$bild_id || !$news_id || !in_array($action, ['delete', 'restore', 'hard_delete'])) {
    die("Ungültige Anfrage. Parameter fehlen.");
}

// --- 3. Aktion ausführen ---
if ($action === 'delete' && $canNewsDelete) {
    $stmt = $pdo->prepare("UPDATE news_bilder SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$bild_id]);
} elseif ($action === 'restore' && $canNewsDeleteHard) { // Nur wer endgültig löschen darf, darf auch wiederherstellen
    $stmt = $pdo->prepare("UPDATE news_bilder SET is_deleted = 0 WHERE id = ?");
    $stmt->execute([$bild_id]);
} elseif ($action === 'hard_delete' && $canNewsDeleteHard) {
    $stmt = $pdo->prepare("SELECT bild_pfad FROM news_bilder WHERE id = ?");
    $stmt->execute([$bild_id]);
    $bild = $stmt->fetchColumn();

    if ($bild) {
        $pfad = __DIR__ . '/../../..' . $bild;
        if (file_exists($pfad)) {
            unlink($pfad);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM news_bilder WHERE id = ?");
    $stmt->execute([$bild_id]);
} else {
    die("Zugriff für diese spezifische Aktion verweigert.");
}

// --- 4. Zurück zur Bearbeiten-Seite ---
header("Location: bearbeiten.php?id=" . $news_id);
exit;