<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_delete_hard'])) {
    die("Zugriff verweigert. Du benötigst das Recht, Bilder endgültig zu löschen.");
}
require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
$return_kat = isset($_GET['return_kat']) ? (int) $_GET['return_kat'] : -1;

if ($id) {
    $stmt = $pdo->prepare("SELECT bild_pfad FROM galerie_bilder WHERE id = ?");
    $stmt->execute([$id]);
    if ($pfad = $stmt->fetchColumn()) {
        $absolut = __DIR__ . '/../../..' . $pfad;
        if (file_exists($absolut))
            unlink($absolut);
        $pdo->prepare("DELETE FROM galerie_bilder WHERE id = ?")->execute([$id]);
    }
}
if ($return_kat >= 0) {
    header("Location: kategorie_details.php?id=" . $return_kat);
} else {
    header("Location: uebersicht.php");
}
exit;