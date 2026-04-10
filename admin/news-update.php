<?php
include_once 'auth.php';
checkLogin();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $titel = trim($_POST['titel']);
    $inhalt = $_POST['inhalt'];

    try {
        // 1. Textdaten aktualisieren
        $sql = "UPDATE news SET titel = ?, inhalt = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titel, $inhalt, $id]);

        // 2. Neue Bilder hochladen (falls vorhanden)
        if (!empty($_FILES['news_bilder']['name'][0])) {
            $uploadDir = 'img/news/';
            foreach ($_FILES['news_bilder']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['news_bilder']['error'][$key] === 0) {
                    $ext = pathinfo($_FILES['news_bilder']['name'][$key], PATHINFO_EXTENSION);
                    $fileName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                    
                    if (move_uploaded_file($tmp_name, $uploadDir . $fileName)) {
                        $stmtB = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");
                        $stmtB->execute([$id, $fileName]);
                    }
                }
            }
        }

        header("Location: news-admin.php");
        exit;
    } catch (PDOException $e) {
        die("Fehler beim Aktualisieren: " . $e->getMessage());
    }
}