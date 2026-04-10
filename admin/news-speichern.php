<?php
include_once 'auth.php';
checkLogin();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = trim($_POST['titel']);
    $inhalt = $_POST['inhalt']; // Hier kein htmlspecialchars, da der Editor HTML erzeugt

    if (!empty($titel)) {
        try {
            // 1. News-Hauptdaten speichern
            $sql = "INSERT INTO news (titel, inhalt, datum) VALUES (?, ?, CURRENT_TIMESTAMP)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titel, $inhalt]);
            
            $news_id = $pdo->lastInsertId();

            // 2. Multi-Bilder-Upload verarbeiten
            if (!empty($_FILES['news_bilder']['name'][0])) {
                $uploadDir = 'img/news/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach ($_FILES['news_bilder']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['news_bilder']['error'][$key] === 0) {
                        $ext = pathinfo($_FILES['news_bilder']['name'][$key], PATHINFO_EXTENSION);
                        $fileName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                        $uploadPath = $uploadDir . $fileName;

                        if (move_uploaded_file($tmp_name, $uploadPath)) {
                            // Pfad in die neue Tabelle news_bilder eintragen
                            $stmtB = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");
                            $stmtB->execute([$news_id, $fileName]);
                        }
                    }
                }
            }
            
            header("Location: news.php");
            exit;
        } catch (PDOException $e) {
            die("Fehler: " . $e->getMessage());
        }
    }
}