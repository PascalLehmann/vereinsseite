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
?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Admin-Dashboard</title>
</head>

<body>

    <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

    <p>Deine Rollen: <?php echo implode(", ", $rollen); ?></p>

    <hr>

    <?php if (hatRolle('admin', $rollen)): ?>
        <h2>Admin-Bereich</h2>
        <ul>
            <li><a href="user_anlegen.php">Neuen Benutzer anlegen</a></li>
            <li><a href="rollen_vergeben.php">Rollen vergeben</a></li>
            <li><a href="system_logs.php">System-Logs</a></li>
        </ul>
    <?php endif; ?>

    <?php if (hatRolle('autor', $rollen)): ?>
        <h2>Autoren-Bereich</h2>
        <ul>
            <li><a href="beitrag_erstellen.php">Beitrag erstellen</a></li>
            <li><a href="beitrag_verwalten.php">Beiträge verwalten</a></li>
        </ul>
    <?php endif; ?>

    <?php if (hatRolle('mitglied', $rollen)): ?>
        <h2>Mitglieder-Bereich</h2>
        <ul>
            <li><a href="profil.php">Mein Profil</a></li>
            <li><a href="vereinsinfos.php">Vereinsinformationen</a></li>
        </ul>
    <?php endif; ?>

    <hr>

    <a href="../logout.php">Logout</a>

</body>

</html>