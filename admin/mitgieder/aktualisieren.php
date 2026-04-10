<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $vorname = $_POST['vorname'];
    $nachname = $_POST['nachname'];
    
    // Altes Bild laden, falls kein neues hochgeladen wird
    $stmt = $pdo->prepare("SELECT profilbild FROM mitglieder WHERE id = ?");
    $stmt->execute([$id]);
    $oldMember = $stmt->fetch();
    $bildName = $oldMember['profilbild'];

    if (!empty($_FILES['profilbild']['name'])) {
        $uploadDir = '../img/mitglieder/';
        $ext = pathinfo($_FILES['profilbild']['name'], PATHINFO_EXTENSION);
        $bildName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
        move_uploaded_file($_FILES['profilbild']['tmp_name'], $uploadDir . $bildName);
        
        // Altes Bild vom Server löschen (optional)
        if ($oldMember['profilbild'] && file_exists($uploadDir . $oldMember['profilbild'])) {
            unlink($uploadDir . $oldMember['profilbild']);
        }
    }

    $sql = "UPDATE mitglieder SET 
        vorname = ?, nachname = ?, profilbild = ?, eintrittsdatum = ?, 
        ist_gruendungsmitglied = ?, im_vorstand = ?, vorstands_rolle = ?,
        best_100_wert = ?, best_100_datum = ?, best_100_ort = ?,
        best_200_wert = ?, best_200_datum = ?, best_200_ort = ?,
        best_120_wert = ?, best_120_datum = ?, best_120_ort = ?
        WHERE id = ?";

    $pdo->prepare($sql)->execute([
        $vorname, $nachname, $bildName,
        $_POST['eintrittsdatum'] ?: null,
        isset($_POST['ist_gruendungsmitglied']) ? 1 : 0,
        isset($_POST['im_vorstand']) ? 1 : 0,
        $_POST['vorstands_rolle'],
        $_POST['best_100_wert'] ?: null, $_POST['best_100_datum'] ?: null, $_POST['best_100_ort'],
        $_POST['best_200_wert'] ?: null, $_POST['best_200_datum'] ?: null, $_POST['best_200_ort'],
        $_POST['best_120_wert'] ?: null, $_POST['best_120_datum'] ?: null, $_POST['best_120_ort'],
        $id
    ]);

    header("Location: mitglieder-admin.php");
    exit;
}