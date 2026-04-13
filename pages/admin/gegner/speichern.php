<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $strasse = trim($_POST['strasse'] ?? '');
    $plz = trim($_POST['plz'] ?? '');
    $ort = trim($_POST['ort'] ?? '');

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO gegner (name, strasse, plz, ort) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $strasse, $plz, $ort]);
    }
    header("Location: übersicht.php?success=1");
    exit;
}
header("Location: übersicht.php");
exit;