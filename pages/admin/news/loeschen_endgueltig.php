<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin'])) {
    die("Zugriff verweigert: Nur Admins können endgültig löschen.");
}
require_once __DIR__ . '/../../../db.php';
$id = $_GET['id'] ?? 0;
if ($id) {
    // Zuerst dazugehörige Bilder auf dem Server löschen
    $stmt = $pdo->prepare("SELECT bild_pfad FROM news_bilder WHERE news_id = ?");
    $stmt->execute([$id]);
    $bilder = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($bilder as $bild) {
        $pfad = __DIR__ . '/../../..' . $bild;
        if (file_exists($pfad))
            unlink($pfad);
    }

    // Dann News komplett aus der Datenbank radieren
    $pdo->prepare("DELETE FROM news_bilder WHERE news_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
}
header("Location: übersicht.php");
exit;