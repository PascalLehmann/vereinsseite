<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Sicherheits-Check: Ist der Gegner noch mit einem Termin verknüpft?
    $check = $pdo->prepare("SELECT COUNT(*) FROM termine WHERE gegner_id = ?");
    $check->execute([$id]);
    
    if ($check->fetchColumn() > 0) {
        // Falls ja: Nicht löschen, sondern mit Fehlermeldung zurück
        header("Location: gegner-admin.php?error=verwendet");
    } else {
        // Falls nein: Löschen
        $stmt = $pdo->prepare("DELETE FROM gegner WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: gegner-admin.php?success=deleted");
    }
} else {
    header("Location: gegner-admin.php");
}
exit;