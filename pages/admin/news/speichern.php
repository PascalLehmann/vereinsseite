<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titel = $_POST['titel'];
    $inhalt = $_POST['inhalt'];

    // News speichern
    $stmt = $pdo->prepare("INSERT INTO news (titel, inhalt, datum) VALUES (?, ?, NOW())");
    $stmt->execute([$titel, $inhalt]);

    $newsId = $pdo->lastInsertId();

    // Bilder speichern
    if (!empty($_FILES['bilder']['name'][0])) {

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/news/';

        foreach ($_FILES['bilder']['tmp_name'] as $key => $tmpName) {

            $fileName = time() . "_" . basename($_FILES['bilder']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $stmtImg = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");
                $stmtImg->execute([$newsId, $fileName]);
            }
        }
    }

    header("Location: /pages/admin/news/übersicht.php");
    exit;
}
