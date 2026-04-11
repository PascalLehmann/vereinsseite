<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Login prüfen
if (empty($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Rollen laden
$stmt = $pdo->prepare("
    SELECT r.name 
    FROM roles r
    JOIN user_roles ur ON ur.role_id = r.id
    WHERE ur.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$rollen = $stmt->fetchAll(PDO::FETCH_COLUMN);

$_SESSION['rollen'] = $rollen;

function hatRolle($rolle, $rollen)
{
    return in_array($rolle, $rollen);
}
?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
</head>

<body>

    <h1>Willkommen, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    <p>Rollen: <?= implode(", ", $rollen) ?></p>

    <hr>

    <?php if (hatRolle('admin', $rollen)): ?>
        <h2>Admin-Bereich</h2>
        <ul>
            <li><a href="user_anlegen.php">Benutzer anlegen</a></li>
            <li><a href="rollen.php">Rollenverwaltung</a></li>
            <li><a href="rollen_vergeben_liste.php">Rollen an Benutzer vergeben</a></li>
        </ul>
    <?php endif; ?>

    <?php if (hatRolle('autor', $rollen)): ?>
        <h2>Autoren-Bereich</h2>
        <ul>
            <li><a href="#">Beitrag erstellen</a></li>
            <li><a href="#">Beiträge verwalten</a></li>
        </ul>
    <?php endif; ?>

    <?php if (hatRolle('mitglied', $rollen)): ?>
        <h2>Mitglieder-Bereich</h2>
        <ul>
            <li><a href="#">Profil</a></li>
            <li><a href="#">Vereinsinfos</a></li>
        </ul>
    <?php endif; ?>

    <hr>
    <a href="../../logout.php">Logout</a>

</body>

</html>