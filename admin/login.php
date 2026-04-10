<?php
include_once 'db.php';
session_start();

$error = "";

// Falls schon eingeloggt, direkt zum Admin-Bereich
if (isset($_SESSION['eingeloggt']) && $_SESSION['eingeloggt'] === true) {
    header("Location: news-admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['eingeloggt'] = true;
        $_SESSION['user_id'] = $admin['id'];
        header("Location: news-admin.php");
        exit;
    } else {
        $error = "Ungültige Zugangsdaten!";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login - CMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; background: var(--secondary-blue);">
    <form action="" method="POST" class="news-card" style="width:100%; max-width:400px;">
        <h2 style="text-align:center;">CMS Login</h2>
        <?php if($error): ?> <p style="color:red;"><?= $error; ?></p> <?php endif; ?>
        
        <label>Benutzername</label>
        <input type="text" name="username" required style="width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
        
        <label>Passwort</label>
        <input type="password" name="password" required style="width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
        
        <button type="submit" class="read-more" style="width:100%; border:none; background:var(--primary-orange); color:white; cursor:pointer;">Einloggen</button>
    </form>
</body>
</html>