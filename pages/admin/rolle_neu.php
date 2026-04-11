<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id']) || !in_array('admin', $_SESSION['rollen'] ?? [])) {
    die("Kein Zugriff.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: rollen.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Neue Rolle</title>
</head>

<body>

    <h1>Neue Rolle erstellen</h1>

    <form method="post">
        <label>Rollenname:
            <input type="text" name="name" required>
        </label>
        <br><br>
        <button type="submit">Speichern</button>
    </form>

</body>

</html>