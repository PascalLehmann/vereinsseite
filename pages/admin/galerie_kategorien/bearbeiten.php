<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_kat_create'])) {
    die("Zugriff verweigert: Du benötigst das Recht, Kategorien zu bearbeiten.");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM galerie_kategorien WHERE id = ?");
$stmt->execute([$id]);
$kat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kat) {
    header("Location: uebersicht.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare("UPDATE galerie_kategorien SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: uebersicht.php");
        exit;
    }
}

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar"><a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück</a></div>
    <h2>Kategorie bearbeiten</h2>

    <div class="content-tile" style="max-width: 500px;">
        <form method="post">
            <div class="form-group">
                <label>Name der Kategorie:</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($kat['name']) ?>"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Änderungen speichern</button>
        </form>
    </div>
</main>