<?php
session_start();
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_kat_delete_hard'])) {
    die("Zugriff verweigert: Du benötigst das Recht zum endgültigen Löschen, um Kategorien wiederherzustellen.");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    $stmt = $pdo->prepare("UPDATE galerie_kategorien SET is_deleted = 0 WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: uebersicht.php");
exit;