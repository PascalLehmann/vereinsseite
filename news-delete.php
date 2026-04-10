<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Alle Bildpfade dieser News holen
    $stmtB = $pdo->prepare("SELECT bild_pfad FROM news_bilder WHERE news_id = ?");
    $stmtB->execute([$id]);
    $bilder = $stmtB->fetchAll();

    // 2. Bilder physisch vom Server löschen
    foreach ($bilder as $bild) {
        $filePath = 'img/news/' . $bild['bild_pfad'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // 3. News aus der Datenbank löschen 
    // (Dank "ON DELETE CASCADE" in der DB werden die Einträge in news_bilder automatisch mitgelöscht)
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: news-admin.php");
exit;