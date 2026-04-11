<?php
session_start();
require_once __DIR__ . '/db.php'; // stellt $pdo bereit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Login fehlgeschlagen.";
    } else {
        // User anhand des Usernames holen
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Prüfen: User existiert + Passwort korrekt?
        if ($user && password_verify($password, $user['password'])) {

            // Session härten
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            header("Location: admin/dashboard.php");
            exit;
        } else {
            $error = "Login fehlgeschlagen.";
        }
    }
}
?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Login</title>
</head>

<body>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Benutzername:
            <input type="text" name="username" required>
        </label><br>

        <label>Passwort:
            <input type="password" name="password" required>
        </label><br>

        <button type="submit">Einloggen</button>
    </form>

</body>

</html>