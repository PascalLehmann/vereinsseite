<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
if (!in_array('admin', $_SESSION['roles'] ?? [])) {
    die("Kein Zugriff.");
}

require_once __DIR__ . '/../../db.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
$stmt->execute([$id]);

header("Location: rollen.php");
exit;
