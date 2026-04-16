<?php
session_start();
if (empty($_SESSION['permissions']['admin'])) {
    die("Zugriff verweigert: Nur für Admins.");
}

require_once __DIR__ . '/../../../db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: uebersicht.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vorstand_positionen WHERE id = ?");
$stmt->execute([$id]);
$pos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pos) {
    header("Location: uebersicht.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare("UPDATE vorstand_positionen SET name = ? WHERE id = ?");
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
    <h2>Position bearbeiten</h2>

    <div class="content-tile" style="max-width: 500px;">
        <form method="post">
            <div class="form-group">
                <label>Name der Position:</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($pos['name']) ?>"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Änderungen speichern</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>