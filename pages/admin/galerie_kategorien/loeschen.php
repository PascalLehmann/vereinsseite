<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_kat_delete'])) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    // Kategorie nur noch verstecken (Soft-Delete)
    $stmt = $pdo->prepare("UPDATE galerie_kategorien SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: uebersicht.php");
exit;