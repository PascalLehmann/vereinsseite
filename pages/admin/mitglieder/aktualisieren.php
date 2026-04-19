<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
$isAdmin = !empty($perms['admin']);
$canEditAll = $isAdmin || !empty($perms['mitglieder_edit']);
$canBestleistungen = $isAdmin || !empty($perms['mitglieder_bestleistungen']);

if (!$canEditAll && !$canBestleistungen) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../image_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];

    // Altes Bild behalten
    $stmt = $pdo->prepare("SELECT profilbild FROM mitglieder WHERE id = ?");
    $stmt->execute([$id]);
    $bildname = $stmt->fetchColumn();

    if ($canEditAll) {
        // Bild-Upload nur ausführen, wenn der Nutzer auch Berechtigungen für das Profilbild hat!
        if (isset($_FILES['profilbild']) && $_FILES['profilbild']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES['profilbild']['tmp_name']);
            $erlaubte_formate = ['image/jpeg', 'image/png', 'image/webp'];

            $dateiendung = strtolower(pathinfo($_FILES['profilbild']['name'], PATHINFO_EXTENSION));
            $erlaubte_endungen = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($mime_type, $erlaubte_formate) || !in_array($dateiendung, $erlaubte_endungen)) {
                die("Sicherheitswarnung: Ungültiges Dateiformat oder Dateiendung.");
            }

            $upload_dir = __DIR__ . '/../../../assets/img/mitglieder/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $bildname = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $dateiendung;
            $ziel_pfad = $upload_dir . $bildname;
            if (!resizeAndCompressImage($_FILES['profilbild']['tmp_name'], $ziel_pfad, 1920, 80)) {
                move_uploaded_file($_FILES['profilbild']['tmp_name'], $ziel_pfad);
            }
        }

        // Admin darf alles ändern
        $sql = "UPDATE mitglieder SET 
                    vorname = ?, nachname = ?, email = ?, telefon = ?, im_vorstand = ?, vorstands_rolle = ?,
                    ist_gruendungsmitglied = ?, ist_aktiv = ?,
                    best_100_wert = ?, best_100_datum = ?, best_100_ort = ?,
                    best_120_wert = ?, best_120_datum = ?, best_120_ort = ?,
                    best_200_wert = ?, best_200_datum = ?, best_200_ort = ?,
                    eintrittsdatum = ?, profilbild = ? 
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['vorname'],
            $_POST['nachname'],
            empty($_POST['email']) ? null : $_POST['email'],
            empty($_POST['telefon']) ? null : $_POST['telefon'],
            isset($_POST['im_vorstand']) ? 1 : 0,
            (isset($_POST['im_vorstand']) && !empty($_POST['vorstands_rolle'])) ? $_POST['vorstands_rolle'] : null,
            isset($_POST['ist_gruendungsmitglied']) ? 1 : 0,
            isset($_POST['ist_aktiv']) ? (int) $_POST['ist_aktiv'] : 1,
            empty($_POST['best_100_wert']) ? null : $_POST['best_100_wert'],
            empty($_POST['best_100_datum']) ? null : $_POST['best_100_datum'],
            empty($_POST['best_100_ort']) ? null : $_POST['best_100_ort'],
            empty($_POST['best_120_wert']) ? null : $_POST['best_120_wert'],
            empty($_POST['best_120_datum']) ? null : $_POST['best_120_datum'],
            empty($_POST['best_120_ort']) ? null : $_POST['best_120_ort'],
            empty($_POST['best_200_wert']) ? null : $_POST['best_200_wert'],
            empty($_POST['best_200_datum']) ? null : $_POST['best_200_datum'],
            empty($_POST['best_200_ort']) ? null : $_POST['best_200_ort'],
            empty($_POST['eintrittsdatum']) ? null : $_POST['eintrittsdatum'],
            $bildname,
            $id
        ]);
    } else {
        // Autor darf NUR die Bestleistungen ändern
        $sql = "UPDATE mitglieder SET 
                    best_100_wert = ?, best_100_datum = ?, best_100_ort = ?,
                    best_120_wert = ?, best_120_datum = ?, best_120_ort = ?,
                    best_200_wert = ?, best_200_datum = ?, best_200_ort = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            empty($_POST['best_100_wert']) ? null : $_POST['best_100_wert'],
            empty($_POST['best_100_datum']) ? null : $_POST['best_100_datum'],
            empty($_POST['best_100_ort']) ? null : $_POST['best_100_ort'],
            empty($_POST['best_120_wert']) ? null : $_POST['best_120_wert'],
            empty($_POST['best_120_datum']) ? null : $_POST['best_120_datum'],
            empty($_POST['best_120_ort']) ? null : $_POST['best_120_ort'],
            empty($_POST['best_200_wert']) ? null : $_POST['best_200_wert'],
            empty($_POST['best_200_datum']) ? null : $_POST['best_200_datum'],
            empty($_POST['best_200_ort']) ? null : $_POST['best_200_ort'],
            $id
        ]);
    }
}
header("Location: uebersicht.php?updated=1");
exit;