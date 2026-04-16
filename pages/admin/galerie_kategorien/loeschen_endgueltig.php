<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_kat_delete_hard'])) {
    die("Zugriff verweigert: Du benötigst das Recht, Kategorien endgültig zu löschen.");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    try {
        $pdo->beginTransaction();

        // 1. Bilder von dieser Kategorie entkoppeln (sie werden nicht gelöscht)
        $stmt = $pdo->prepare("UPDATE galerie_bilder SET kategorie_id = NULL WHERE kategorie_id = ?");
        $stmt->execute([$id]);

        // 2. Kategorie endgültig aus der Datenbank löschen
        $stmt = $pdo->prepare("DELETE FROM galerie_kategorien WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Fehler beim endgültigen Löschen der Kategorie: " . $e->getMessage());
    }
}
header("Location: uebersicht.php");
exit;