<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$canTermineEdit = !empty($perms['admin']) || !empty($perms['termine_edit']);

if (!$canTermineEdit) {
    die("Zugriff verweigert.");
}

// ... hier folgt dann dein restlicher Code


require_once __DIR__ . '/../../../db.php';

// Hilfsfunktion: Wandelt leere Strings in echte NULL-Werte für die DB um
function setNullIfEmpty($val)
{
    return (empty($val) && $val !== '0') ? null : $val;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE termine SET 
        typ = :typ, termin_datum = :termin_datum, uhrzeit = :uhrzeit, titel = :titel, 
        veranstaltungsart = :veranstaltungsart, beschreibung = :beschreibung, ort = :ort, 
        gegner_id = :gegner_id, heimspiel = :heimspiel, treffpunkt_zeit = :treffpunkt_zeit, treffpunkt_ort = :treffpunkt_ort, spielfuehrer_id = :spielfuehrer_id, 
        s1 = :s1, s2 = :s2, s3 = :s3, s4 = :s4, s5 = :s5, s6 = :s6, 
        a1 = :a1, a2 = :a2, a3 = :a3
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':typ' => $_POST['typ'] ?? 'veranstaltung',
        ':termin_datum' => setNullIfEmpty($_POST['termin_datum']),
        ':uhrzeit' => setNullIfEmpty($_POST['uhrzeit']),
        ':titel' => trim($_POST['titel'] ?? ''),
        ':veranstaltungsart' => setNullIfEmpty($_POST['veranstaltungsart'] ?? ''),
        ':beschreibung' => setNullIfEmpty($_POST['beschreibung'] ?? ''),
        ':ort' => setNullIfEmpty($_POST['ort'] ?? ''),
        ':gegner_id' => setNullIfEmpty($_POST['gegner_id'] ?? null),
        ':heimspiel' => isset($_POST['heimspiel']) ? 1 : 0,
        ':treffpunkt_zeit' => setNullIfEmpty($_POST['treffpunkt_zeit'] ?? null),
        ':treffpunkt_ort' => setNullIfEmpty($_POST['treffpunkt_ort'] ?? null),
        ':spielfuehrer_id' => setNullIfEmpty($_POST['spielfuehrer_id'] ?? null),
        ':s1' => setNullIfEmpty($_POST['s1'] ?? null),
        ':s2' => setNullIfEmpty($_POST['s2'] ?? null),
        ':s3' => setNullIfEmpty($_POST['s3'] ?? null),
        ':s4' => setNullIfEmpty($_POST['s4'] ?? null),
        ':s5' => setNullIfEmpty($_POST['s5'] ?? null),
        ':s6' => setNullIfEmpty($_POST['s6'] ?? null),
        ':a1' => setNullIfEmpty($_POST['a1'] ?? null),
        ':a2' => setNullIfEmpty($_POST['a2'] ?? null),
        ':a3' => setNullIfEmpty($_POST['a3'] ?? null),
        ':id' => (int) $_POST['id']
    ]);

    header("Location: uebersicht.php?updated=1");
    exit;
}