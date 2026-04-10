<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typ = $_POST['typ'];
    $datum = $_POST['termin_datum'];
    $uhrzeit = $_POST['uhrzeit'];
    
    // Spieltag-spezifische Felder
    $gegner_id = $_POST['gegner_id'] ?? null;
    $spielfuehrer_id = $_POST['spielfuehrer_id'] ?? null;
    $treffpunkt_zeit = $_POST['treffpunkt_zeit'] ?? null;
    
    // Aufstellung (s1-s6, a1-a3)
    $s1 = $_POST['s1']; $s2 = $_POST['s2']; $s3 = $_POST['s3'];
    $s4 = $_POST['s4']; $s5 = $_POST['s5']; $s6 = $_POST['s6'];
    $a1 = $_POST['a1']; $a2 = $_POST['a2']; $a3 = $_POST['a3'];

    $sql = "INSERT INTO termine 
            (typ, termin_datum, uhrzeit, gegner_id, spielfuehrer_id, treffpunkt_zeit, s1, s2, s3, s4, s5, s6, a1, a2, a3) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $typ, $datum, $uhrzeit, $gegner_id, $spielfuehrer_id, $treffpunkt_zeit,
        $s1, $s2, $s3, $s4, $s5, $s6, $a1, $a2, $a3
    ]);

    header("Location: termine-admin.php");
    exit;
}