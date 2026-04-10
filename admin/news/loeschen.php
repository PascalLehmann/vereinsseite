<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if (isset($_GET['id'])) {
    // Bild-Datei löschen
    $stmt = $pdo->prepare("SELECT bild FROM news WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $bild = $stmt->fetchColumn();
    if ($bild && file_exists("../../img/news/" . $bild)) {
        unlink("../../img/news/" . $bild);
    }

    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: übersicht.php");
exit;