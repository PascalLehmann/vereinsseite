<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$id = (int) $_POST['id'];
$titel = $_POST['titel'];
$inhalt = $_POST['inhalt'];

$stmt = $pdo->prepare("UPDATE news SET titel = ?, inhalt = ? WHERE id = ?");
$stmt->execute([$titel, $inhalt, $id]);

// Bilder speichern
if (!empty($_FILES['bilder']['name'][0])) {

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/news/';

    foreach ($_FILES['bilder']['tmp_name'] as $key => $tmpName) {

        $fileName = time() . "_" . basename($_FILES['bilder']['name'][$key]);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $stmtImg = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");
            $stmtImg->execute([$id, $fileName]);
        }
    }
}

header("Location: /pages/admin/news/übersicht.php");
exit;
