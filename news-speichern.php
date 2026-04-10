<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = trim($_POST['titel']);
    $inhalt = trim($_POST['inhalt']);
    $bildName = 'default.jpg'; // Standardbild, falls kein Upload erfolgt

    // BILD-UPLOAD LOGIK
    if (isset($_FILES['news_bild']) && $_FILES['news_bild']['error'] === 0) {
        $uploadDir = 'img/news/'; // Ordner auf dem Server
        
        // Ordner erstellen, falls er nicht existiert
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = pathinfo($_FILES['news_bild']['name'], PATHINFO_EXTENSION);
        // Eindeutiger Name: Zeitstempel + Titel-Slug
        $bildName = time() . "_" . bin2hex(random_bytes(4)) . "." . $fileExtension;
        $uploadPath = $uploadDir . $bildName;

        // Datei physisch verschieben
        if (!move_uploaded_file($_FILES['news_bild']['tmp_name'], $uploadPath)) {
            $bildName = 'default.jpg'; // Falls Upload fehlschlägt
        }
    }

    if (!empty($titel) && !empty($inhalt)) {
        try {
            $sql = "INSERT INTO news (titel, inhalt, bild, datum) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titel, $inhalt, $bildName]);
            
            header("Location: news.php");
            exit;
        } catch (PDOException $e) {
            die("Fehler: " . $e->getMessage());
        }
    }
}