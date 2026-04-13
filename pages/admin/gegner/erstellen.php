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

$error = '';
$success = '';

$pageTitle = "Gegner hinzufügen";

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';

?>

<main>
    <h2>Neuen Gegner anlegen</h2>

    <div style="margin-bottom: 20px;">
        <a href="übersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="speichern.php" method="POST" style="max-width: 600px;">
        <div class="form-group">
            <label>Vereinsname:</label>
            <input type="text" name="name" class="form-control" required placeholder="z.B. Alle Neune e.V.">
        </div>
        <div style="display:grid; grid-template-columns: 2fr 1fr 2fr; gap:15px;">
            <div class="form-group">
                <label>Straße:</label>
                <input type="text" name="strasse" class="form-control">
            </div>
            <div class="form-group">
                <label>PLZ:</label>
                <input type="text" name="plz" class="form-control">
            </div>
            <div class="form-group">
                <label>Ort:</label>
                <input type="text" name="ort" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Gegner speichern</button>
    </form>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>