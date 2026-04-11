<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if (isset($_GET['id'])) {
    // Optional: Altes Bild vom Server löschen
    $stmt = $pdo->prepare("SELECT profilbild FROM mitglieder WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $bild = $stmt->fetchColumn();
    if ($bild && file_exists("../../img/mitglieder/" . $bild)) {
        unlink("../../img/mitglieder/" . $bild);
    }

    $stmt = $pdo->prepare("DELETE FROM mitglieder WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: übersicht.php");
exit;