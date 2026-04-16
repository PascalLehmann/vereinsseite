<?php
session_start();
if (empty($_SESSION['permissions']['admin'])) {
    die("Zugriff verweigert: Nur für Admins.");
}

require_once __DIR__ . '/../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $max_order = $pdo->query("SELECT MAX(sort_order) FROM vorstand_positionen")->fetchColumn();
        $new_order = ($max_order === null) ? 1 : $max_order + 1;

        $stmt = $pdo->prepare("INSERT INTO vorstand_positionen (name, sort_order) VALUES (?, ?)");
        $stmt->execute([$name, $new_order]);
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
    <h2>Neue Position erstellen</h2>

    <div class="content-tile" style="max-width: 500px;">
        <form method="post">
            <div class="form-group">
                <label>Name der Position:</label>
                <input type="text" name="name" class="form-control" required placeholder="z.B. Beisitzer">
            </div>
            <button type="submit" class="btn btn-primary">Position speichern</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>