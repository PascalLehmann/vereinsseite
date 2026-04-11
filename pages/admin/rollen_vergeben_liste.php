<?php
session_start();
require_once __DIR__ . '/../../db.php';

if (!in_array('admin', $_SESSION['rollen'])) {
    die("Kein Zugriff.");
}

$users = $pdo->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rollen vergeben</title>
</head>

<body>

    <h1>Rollen an Benutzer vergeben</h1>

    <ul>
        <?php foreach ($users as $u): ?>
            <li>
                <a href="rollen_vergeben.php?id=<?= $u['id'] ?>">
                    <?= htmlspecialchars($u['username']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

</body>

</html>