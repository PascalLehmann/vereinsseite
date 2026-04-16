<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_kat_create'])) {
    die("Zugriff verweigert: Du benötigst das Recht, Kategorien zu erstellen.");
}

require_once __DIR__ . '/../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO galerie_kategorien (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: uebersicht.php");
        exit;
    }
}

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück</a>
    </div>
    <h2>Neue Kategorie erstellen</h2>

    <div class="content-tile" style="max-width: 500px;">
        <form method="post">
            <div class="form-group">
                <label>Name der Kategorie (z.B. Sommerfest 2025):</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Kategorie speichern</button>
        </form>
    </div>
</main>