<?php
// Session-Sicherheit: Cookie-Pfad auf Root setzen, damit nav.php sie überall lesen kann
session_set_cookie_params(0, '/');
session_start();

include_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Tabelle 'users' wie auf deinem Screenshot
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Wir setzen die ID, die die nav.php abfragt
        $_SESSION['admin_id'] = $user['id'];
        session_write_close(); // Sicherstellen, dass die Session gespeichert wird
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Login fehlgeschlagen!";
    }
}
?>