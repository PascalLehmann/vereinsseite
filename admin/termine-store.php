<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO termine (
        typ, termin_datum, uhrzeit, titel, beschreibung, ort, 
        gegner_id, heimspiel, treffpunkt_zeit, spielfuehrer_id, 
        s1, s2, s3, s4, s5, s6, a1, a2, a3
    ) VALUES (
        ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $pdo->prepare($sql);
    
    // Wir setzen leere Strings auf NULL für die Datenbank
    $params = [
        $_POST['typ'],
        $_POST['termin_datum'],
        $_POST['uhrzeit'],
        $_POST['titel'] ?: null,
        $_POST['beschreibung'] ?: null,
        $_POST['ort'] ?: null,
        $_POST['gegner_id'] ?: null,
        $_POST['heimspiel'] ?? 1,
        $_POST['treffpunkt_zeit'] ?: null,
        $_POST['spielfuehrer_id'] ?: null,
        $_POST['s1'] ?: null, $_POST['s2'] ?: null, $_POST['s3'] ?: null,
        $_POST['s4'] ?: null, $_POST['s5'] ?: null, $_POST['s6'] ?: null,
        $_POST['a1'] ?: null, $_POST['a2'] ?: null, $_POST['a3'] ?: null
    ];

    $stmt->execute($params);

    header("Location: termine-admin.php?success=1");
    exit;
}