<?php
session_start();
require_once __DIR__ . '/../db.php';

// 1. Login prüfen
if (empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Rollen laden
$stmt = $pdo->prepare("
    SELECT r.name 
    FROM roles r
    JOIN user_roles ur ON ur.role_id = r.id
    WHERE ur.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$rollen = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 3. Hilfsfunktion
function hatRolle($rolle, $rollen)
{
    return in_array($rolle, $rollen);
}

// 4. Rollen in Session speichern (optional)
$_SESSION['rollen'] = $rollen;
?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        h2 {
            margin-top: 0;
        }

        ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>

    <h1>Willkommen, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    <p>Deine Rollen: <strong><?= implode(", ", $rollen) ?></strong></p>

    <hr>

    <!-- ADMIN-BEREICH -->
    <?php if (hatRolle('admin', $rollen)): ?>
        <div class="box">
            <h2>🔧 Admin-Bereich</h2>
            <ul>
                <li><a href="user_anlegen.php">Neuen Benutzer anlegen</a></li>
                <li><a href="rollen.php">Rollenverwaltung</a></li>
                <li><a href="rollen_vergeben_liste.php">Rollen an Benutzer vergeben</a></li>
                <li><a href="system_logs.php">System-Logs</a></li>
                <li><a href="einstellungen.php">System-Einstellungen</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- AUTOR-BEREICH -->
    <?php if (hatRolle('autor', $rollen)): ?>
        <div class="box">
            <h2>✍️ Autoren-Bereich</h2>
            <ul>
                <li><a href="beitrag_erstellen.php">Beitrag erstellen</a></li>
                <li><a href="beitrag_verwalten.php">Beiträge verwalten</a></li>
                <li><a href="mediathek.php">Mediathek</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- MITGLIEDER-BEREICH -->
    <?php if (hatRolle('mitglied', $rollen)): ?>
        <div class="box">
            <h2>👥 Mitglieder-Bereich</h2>
            <ul>
                <li><a href="profil.php">Mein Profil</a></li>
                <li><a href="vereinsinfos.php">Vereinsinformationen</a></li>
                <li><a href="termine.php">Termine & Veranstaltungen</a></li>
                <li><a href="downloads.php">Downloads</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <hr>

    <a href="../logout.php">Logout</a>

</body>

</html>