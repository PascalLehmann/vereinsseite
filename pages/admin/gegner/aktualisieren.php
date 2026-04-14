<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    $spielzeit = !empty($_POST['spielzeit']) ? $_POST['spielzeit'] : null;
    $bahnen = trim($_POST['bahnen'] ?? '');

    $stmt = $pdo->prepare("UPDATE gegner SET name = ?, strasse = ?, plz = ?, ort = ?, spielzeit = ?, bahnen = ? WHERE id = ?");
    $stmt->execute([$_POST['name'], $_POST['strasse'], $_POST['plz'], $_POST['ort'], $spielzeit, $bahnen, $_POST['id']]);
}
header("Location: übersicht.php?updated=1");
exit;