<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bildname = null;
    if (!empty($_FILES['profilbild']['name'])) {
        $bildname = time() . "_" . $_FILES['profilbild']['name'];
        move_uploaded_file($_FILES['profilbild']['tmp_name'], "../../img/mitglieder/" . $bildname);
    }

    $sql = "INSERT INTO mitglieder (
                vorname, nachname, im_vorstand, vorstands_rolle, 
                ist_gruendungsmitglied,
                best_100_wert, best_100_datum, best_100_ort,
                best_120_wert, best_120_datum, best_120_ort,
                best_200_wert, best_200_datum, best_200_ort,
                eintrittsdatum, profilbild
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['vorname'],
        $_POST['nachname'],
        isset($_POST['im_vorstand']) ? 1 : 0,
        $_POST['vorstands_rolle'] ?: null,
        isset($_POST['ist_gruendungsmitglied']) ? 1 : 0, // Das neue Feld
        $_POST['best_100_wert'] ?: null,
        $_POST['best_100_datum'] ?: null,
        $_POST['best_100_ort'] ?: null,
        $_POST['best_120_wert'] ?: null,
        $_POST['best_120_datum'] ?: null,
        $_POST['best_120_ort'] ?: null,
        $_POST['best_200_wert'] ?: null,
        $_POST['best_200_datum'] ?: null,
        $_POST['best_200_ort'] ?: null,
        $_POST['eintrittsdatum'] ?: null,
        $bildname
    ]);
}
header("Location: übersicht.php");
exit;