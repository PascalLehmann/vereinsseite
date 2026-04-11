<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $news_id = $_POST['id'];
    $titel = $_POST['titel'];
    $inhalt = $_POST['inhalt'];

    // 1. Textdaten aktualisieren
    $stmt = $pdo->prepare("UPDATE news SET titel = ?, inhalt = ? WHERE id = ?");
    $stmt->execute([$titel, $inhalt, $news_id]);

    // 2. Markierte Bilder löschen
    if (!empty($_POST['delete_bilder'])) {
        foreach ($_POST['delete_bilder'] as $bild_id) {
            // Erst Dateiname holen, um Datei vom Server zu löschen
            $stmtGet = $pdo->prepare("SELECT bild_pfad FROM news_bilder WHERE id = ?");
            $stmtGet->execute([$bild_id]);
            $rawName = $stmtGet->fetchColumn();

            if ($rawName) {
                // Pfad-Säuberung (entfernt eventuelle Präfixe aus der DB)
                $cleanName = str_replace('img/news/', '', $rawName);
                $dateiPfad = "../../img/news/" . $cleanName;

                if (file_exists($dateiPfad)) {
                    unlink($dateiPfad); // Datei vom Webspace löschen
                }
            }

            // Eintrag aus der Datenbank entfernen
            $stmtDel = $pdo->prepare("DELETE FROM news_bilder WHERE id = ?");
            $stmtDel->execute([$bild_id]);
        }
    }

    // 3. Neue Bilder zur bestehenden Galerie hinzufügen
    if (!empty($_FILES['bilder']['name'][0])) {
        foreach ($_FILES['bilder']['tmp_name'] as $key => $tmp_name) {
            $originalName = $_FILES['bilder']['name'][$key];
            $dateiname = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);

            $zielPfad = "../../img/news/" . $dateiname;

            if (move_uploaded_file($tmp_name, $zielPfad)) {
                $stmtNew = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");
                $stmtNew->execute([$news_id, $dateiname]);
            }
        }
    }
}

header("Location: uebersicht.php?updated=1");
exit;