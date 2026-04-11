<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if (isset($_GET['id'])) {
    $news_id = $_GET['id'];

    // 1. Alle Bilder-Dateien dieser News vom Server löschen
    $stmt = $pdo->prepare("SELECT dateiname FROM news_bilder WHERE news_id = ?");
    $stmt->execute([$news_id]);
    $bilder = $stmt->fetchAll();

    foreach ($bilder as $bild) {
        $datei = "../../img/news/" . $bild['dateiname'];
        if (file_exists($datei)) {
            unlink($datei);
        }
    }

    // 2. News löschen (news_bilder werden durch ON DELETE CASCADE in der DB automatisch gelöscht)
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$news_id]);
}

header("Location: uebersicht.php?deleted=1");
exit;