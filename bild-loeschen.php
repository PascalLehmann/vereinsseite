<?php
include 'db.php';

$bild_id = (int)$_GET['bild_id'];
$news_id = (int)$_GET['news_id'];

if ($bild_id > 0) {
    // 1. Dateiname holen, um Datei vom Server zu löschen
    $stmt = $pdo->prepare("SELECT bild_pfad FROM news_bilder WHERE id = ?");
    $stmt->execute([$bild_id]);
    $bild = $stmt->fetch();

    if ($bild) {
        $filePath = 'img/news/' . $bild['bild_pfad'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // 2. Eintrag aus Datenbank löschen
        $stmtDel = $pdo->prepare("DELETE FROM news_bilder WHERE id = ?");
        $stmtDel->execute([$bild_id]);
    }
}

// Zurück zur Bearbeiten-Seite
header("Location: news-edit.php?id=" . $news_id);
exit;