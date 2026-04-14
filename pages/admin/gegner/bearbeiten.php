<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert.");
}

// 2. DATENBANK EINBINDEN
require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM gegner WHERE id = ?");
$stmt->execute([$id]);
$g = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$g) {
    header("Location: uebersicht.php");
    exit;
}

$pageTitle = "Gegner bearbeiten";

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <h2>Gegner bearbeiten</h2>

    <div class="content-tile" style="max-width: 600px;">
        <form action="aktualisieren.php" method="POST">
            <input type="hidden" name="id" value="<?= $g['id'] ?>">

            <div class="form-group">
                <label>Vereinsname:</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($g['name']) ?>"
                    required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Standard-Spielzeit (Heimspiel des Gegners):</label>
                    <input type="time" name="spielzeit" class="form-control"
                        value="<?= htmlspecialchars($g['spielzeit'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Zu spielende Bahnen:</label>
                    <input type="text" name="bahnen" class="form-control"
                        value="<?= htmlspecialchars($g['bahnen'] ?? '') ?>" placeholder="z.B. 1-4">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr 2fr; gap:15px;">
                <div class="form-group">
                    <label>Straße:</label>
                    <input type="text" name="strasse" class="form-control"
                        value="<?= htmlspecialchars($g['strasse']) ?>">
                </div>
                <div class="form-group">
                    <label>PLZ:</label>
                    <input type="text" name="plz" class="form-control" value="<?= htmlspecialchars($g['plz']) ?>">
                </div>
                <div class="form-group">
                    <label>Ort:</label>
                    <input type="text" name="ort" class="form-control" value="<?= htmlspecialchars($g['ort']) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Änderungen speichern</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>