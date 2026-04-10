<?php
include_once 'auth.php'; checkLogin(); include_once '../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO gegner (name, strasse, plz, ort) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['strasse'], $_POST['plz'], $_POST['ort']]);
    header("Location: gegner-admin.php");
}