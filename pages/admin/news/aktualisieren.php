<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
$canNewsEdit = !empty($perms['news_edit']);
if (!$canNewsEdit) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../image_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $titel = trim($_POST['titel'] ?? '');

    $inhalt = trim($_POST['inhalt'] ?? '');

    $is_spielbericht = isset($_POST['is_spielbericht']) ? 1 : 0;
    $sw_saison_id = $_POST['sw_saison_id'] ?? null;
    $sw_liga_id = $_POST['sw_liga_id'] ?? null;
    $sw_spieltag = !empty($_POST['sw_spieltag']) ? (int) $_POST['sw_spieltag'] : null;

    if (empty($titel) || empty($inhalt)) {
        die("Titel und Inhalt dürfen nicht leer sein.");
    }

    try {
        // ATOMARE TRANSAKTION STARTEN
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE news SET titel = ?, inhalt = ?, is_spielbericht = ?, sw_saison_id = ?, sw_liga_id = ?, sw_spieltag = ? WHERE id = ?");
        $stmt->execute([$titel, $inhalt, $is_spielbericht, $sw_saison_id, $sw_liga_id, $sw_spieltag, $id]);

        // Sicheren Bildupload wie in erstellen.php verarbeiten
        if (isset($_FILES['bilder']) && $_FILES['bilder']['error'][0] !== UPLOAD_ERR_NO_FILE) {
            $upload_dir = __DIR__ . '/../../../uploads/news/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $erlaubte_formate = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 5 * 1024 * 1024;
            $stmtImg = $pdo->prepare("INSERT INTO news_bilder (news_id, bild_pfad) VALUES (?, ?)");

            $count = count($_FILES['bilder']['name']);
            for ($i = 0; $i < $count; $i++) {
                $tmp_name = $_FILES['bilder']['tmp_name'][$i];
                $error_code = $_FILES['bilder']['error'][$i];
                $size = $_FILES['bilder']['size'][$i];

                if ($error_code === UPLOAD_ERR_OK) {
                    if ($size > $max_size)
                        throw new Exception("Ein Bild ist größer als 5MB.");

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime_type = $finfo->file($tmp_name);
                    if (!in_array($mime_type, $erlaubte_formate))
                        throw new Exception("Falsches Format.");

                    $dateiendung = strtolower(pathinfo($_FILES['bilder']['name'][$i], PATHINFO_EXTENSION));
                    $erlaubte_endungen = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($dateiendung, $erlaubte_endungen)) {
                        throw new Exception("Die Dateiendung ist nicht erlaubt.");
                    }

                    $neuer_dateiname = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $dateiendung;
                    $ziel_pfad_absolut = $upload_dir . $neuer_dateiname;
                    $ziel_pfad_db = '/uploads/news/' . $neuer_dateiname;

                    if (resizeAndCompressImage($tmp_name, $ziel_pfad_absolut, 1920, 80) || move_uploaded_file($tmp_name, $ziel_pfad_absolut)) {
                        $stmtImg->execute([$id, $ziel_pfad_db]);
                    }
                }
            }
        }

        $pdo->commit();
        header("Location: uebersicht.php?updated=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Fehler beim Aktualisieren: " . $e->getMessage());
    }
}
