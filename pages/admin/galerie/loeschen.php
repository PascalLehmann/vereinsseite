<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin']) && empty($perms['news'])) {
    die("Zugriff verweigert.");
}
require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    $stmt = $pdo->prepare("SELECT bild_pfad FROM galerie_bilder WHERE id = ?");
    $stmt->execute([$id]);
    $pfad = $stmt->fetchColumn();

    if ($pfad) {
        // Löscht die Datei physisch vom Server
        $absolut = __DIR__ . '/../../..' . $pfad;
        if (file_exists($absolut))
            unlink($absolut);
        // Löscht den Eintrag aus der Datenbank
        $pdo->prepare("DELETE FROM galerie_bilder WHERE id = ?")->execute([$id]);
    }
}
header("Location: uebersicht.php");
exit;