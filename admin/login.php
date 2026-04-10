<?php
// 1. Session MUSS ganz oben stehen
session_start();
include_once '../db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Abfrage auf deine Tabelle 'users'
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 2. Session-Variablen setzen
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['logged_in'] = true;

        // 3. Erfolg: Umleitung zum Dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Benutzername oder Passwort falsch!";
    }
}
?>