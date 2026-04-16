<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$canGalerieDelete = !empty($perms['galerie_delete']);

if (!$canGalerieDelete) {
    die("Zugriff verweigert. Du benötigst das Recht, Bilder zu löschen.");
}
require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
$return_kat = isset($_GET['return_kat']) ? (int) $_GET['return_kat'] : -1;

if ($id) {
    $pdo->prepare("UPDATE galerie_bilder SET is_deleted = 1 WHERE id = ?")->execute([$id]);
}
if ($return_kat >= 0) {
    header("Location: kategorie_details.php?id=" . $return_kat);
} else {
    header("Location: uebersicht.php");
}
exit;