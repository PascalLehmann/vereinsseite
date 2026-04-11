<?php
session_start();
require_once __DIR__ . '/../../db.php';

if (!in_array('admin', $_SESSION['rollen'])) {
    die("Kein Zugriff.");
}

$rollen = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rollenverwaltung</title>
</head>

<body>

    <h1>Rollenverwaltung</h1>

    <a href="rolle_neu.php">Neue Rolle erstellen</a>
    <br><br>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Aktionen</th>
        </tr>

        <?php foreach ($rollen as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>
                    <a href="rollen_bearbeiten.php?id=<?= $r['id'] ?>">Bearbeiten</a> |
                    <a href="rollen_loeschen.php?id=<?= $r['id'] ?>"
                        onclick="return confirm('Wirklich löschen?')">Löschen</a>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

</body>

</html>