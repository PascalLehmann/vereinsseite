<?php
session_start();
require_once __DIR__ . '/../db.php';

// Nur Admins dürfen hier rein
if (empty($_SESSION['user_id']) || !in_array('admin', $_SESSION['rollen'] ?? [])) {
    die("Kein Zugriff.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rollen = $_POST['rollen'] ?? [];

    if ($username !== '' && $password !== '') {

        // User anlegen
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);

        $userId = $pdo->lastInsertId();

        // Rollen zuweisen
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        foreach ($rollen as $roleId) {
            $stmt->execute([$userId, $roleId]);
        }

        echo "Benutzer erfolgreich angelegt.";
    }
}

// Rollen laden
$rollenStmt = $pdo->query("SELECT * FROM roles");
$alleRollen = $rollenStmt->fetchAll();
?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Benutzer anlegen</title>
</head>

<body>

    <h1>Neuen Benutzer anlegen</h1>

    <form method="post">
        <label>Benutzername:
            <input type="text" name="username" required>
        </label><br>

        <label>Passwort:
            <input type="password" name="password" required>
        </label><br>

        <label>Rollen:</label><br>
        <?php foreach ($alleRollen as $r): ?>
            <label>
                <input type="checkbox" name="rollen[]" value="<?php echo $r['id']; ?>">
                <?php echo htmlspecialchars($r['name']); ?>
            </label><br>
        <?php endforeach; ?>

        <button type="submit">Benutzer anlegen</button>
    </form>

</body>

</html>