<?php
session_start();
require_once __DIR__ . '/../../db.php';

if (empty($_SESSION['rollen']) || !in_array('admin', $_SESSION['rollen'])) {
    die("Kein Zugriff.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rollen = $_POST['rollen'] ?? [];

    if ($username !== '' && $password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);

        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        foreach ($rollen as $rid) {
            $stmt->execute([$userId, $rid]);
        }

        header("Location: dashboard.php");
        exit;
    }
}

$rollenStmt = $pdo->query("SELECT * FROM roles");
$alleRollen = $rollenStmt->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Benutzer anlegen</title>
</head>

<body>

    <h1>Benutzer anlegen</h1>

    <form method="post">
        <label>Benutzername:
            <input type="text" name="username" required>
        </label><br>

        <label>Passwort:
            <input type="password" name="password" required>
        </label><br>

        <p>Rollen:</p>
        <?php foreach ($alleRollen as $r): ?>
            <label>
                <input type="checkbox" name="rollen[]" value="<?= $r['id'] ?>">
                <?= htmlspecialchars($r['name']) ?>
            </label><br>
        <?php endforeach; ?>

        <br>
        <button type="submit">Speichern</button>
    </form>

</body>

</html>