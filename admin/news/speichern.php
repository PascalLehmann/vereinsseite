<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = $_POST['titel'];
    $inhalt = $_POST['inhalt']; // HTML vom CKEditor

    // 1. News-Beitrag in der Haupttabelle speichern
    $stmt = $pdo->prepare("INSERT INTO news (titel, inhalt, datum) VALUES (?, ?, NOW())");
    $stmt->execute([$titel, $inhalt]);
    $news_id = $pdo->lastInsertId();

    // 2. Mehrfach-Bilder-Upload verarbeiten
    if (!empty($_FILES['bilder']['name'][0])) {
        foreach ($_FILES['bilder']['tmp_name'] as $key => $tmp_name) {
            $originalName = $_FILES['bilder']['name'][$key];
            // Dateiname bereinigen und Zeitstempel für Eindeutigkeit hinzufügen
            $dateiname = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);

            $zielPfad = "../../img/news/" . $dateiname;

            if (move_uploaded_file($tmp_name, $zielPfad)) {
                // In news_bilder speichern (Spalte: bild_pfad)
                $stmtBilder = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");
                $stmtBilder->execute([$news_id, $dateiname]);
            }
        }
    }
}

// Zurück zur Übersicht mit Erfolgshinsweis
header("Location: uebersicht.php?success=1");
exit;