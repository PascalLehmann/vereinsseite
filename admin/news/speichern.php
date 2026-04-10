<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. News Text speichern
    $stmt = $pdo->prepare("INSERT INTO news (titel, inhalt, datum) VALUES (?, ?, NOW())");
    $stmt->execute([$_POST['titel'], $_POST['inhalt']]);
    $news_id = $pdo->lastInsertId();

    // 2. Bilder verarbeiten
    if (!empty($_FILES['bilder']['name'][0])) {
        foreach ($_FILES['bilder']['tmp_name'] as $key => $tmp_name) {
            $dateiname = time() . "_" . $_FILES['bilder']['name'][$key];
            if (move_uploaded_file($tmp_name, "../../img/news/" . $dateiname)) {
                $stmt = $pdo->prepare("INSERT INTO news_bilder (news_id, dateiname) VALUES (?, ?)");
                $stmt->execute([$news_id, $dateiname]);
            }
        }
    }
}
header("Location: übersicht.php");
exit;