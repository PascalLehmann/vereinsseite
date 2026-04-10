<?php
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
        // Session setzen
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_user'] = $user['username'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Ungültige Anmeldedaten!";
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#ff8c00; margin:0;">
    <form action="login.php" method="POST"
        style="background:white; padding:40px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.2); width:100%; max-width:400px;">
        <h2 style="margin-bottom:20px; color:#2c3e50;">Admin Login</h2>

        <?php if ($error): ?>
            <p style="background:#ffcccc; color:#c0392b; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $error ?>
            </p>
        <?php endif; ?>

        <div style="margin-bottom:15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Benutzername</label>
            <input type="text" name="username" required
                style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Passwort</label>
            <input type="password" name="password" required
                style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
        </div>

        <button type="submit"
            style="width:100%; padding:12px; background:#2c3e50; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Einloggen</button>
        <p style="margin-top:20px; text-align:center;"><a href="../index.php"
                style="color:#666; text-decoration:none; font-size:0.9rem;">Zurück zur Website</a></p>
    </form>
</body>

</html>