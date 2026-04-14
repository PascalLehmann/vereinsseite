<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles)) {
    die("<div class='alert-error' style='margin: 20px;'>Zugriff verweigert: Du hast keine Administrator-Rechte.</div>");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$id]);
$rolle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rolle) {
    die("<div class='alert-error' style='margin: 20px;'>Rolle nicht gefunden.</div>");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $perm_news = isset($_POST['perm_news']) ? 1 : 0;
    $perm_termine = isset($_POST['perm_termine']) ? 1 : 0;
    $perm_bestleistungen = isset($_POST['perm_bestleistungen']) ? 1 : 0;
    $perm_admin = isset($_POST['perm_admin']) ? 1 : 0;

    if ($name !== '') {
        try {
            $stmt = $pdo->prepare("UPDATE roles SET name = ?, perm_news = ?, perm_termine = ?, perm_bestleistungen = ?, perm_admin = ? WHERE id = ?");
            $stmt->execute([$name, $perm_news, $perm_termine, $perm_bestleistungen, $perm_admin, $id]);
            header("Location: uebersicht.php");
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Speichern: " . $e->getMessage();
        }
    } else {
        $error = "Bitte gib einen Rollennamen ein.";
    }
}

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <h2>Rolle bearbeiten</h2>

    <div class="content-tile">
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="name">Rollenname:</label>
                <input type="text" id="name" name="name" class="form-control"
                    value="<?= htmlspecialchars($rolle['name']) ?>" required>
            </div>

            <h3 style="margin-top: 20px;">Berechtigungen</h3>
            <div class="form-group">
                <label style="font-weight: normal;"><input type="checkbox" name="perm_news" value="1"
                        <?= !empty($rolle['perm_news']) ? 'checked' : '' ?>> News erstellen & bearbeiten</label>
            </div>
            <div class="form-group">
                <label style="font-weight: normal;"><input type="checkbox" name="perm_termine" value="1"
                        <?= !empty($rolle['perm_termine']) ? 'checked' : '' ?>> Termine erstellen & bearbeiten</label>
            </div>
            <div class="form-group">
                <label style="font-weight: normal;"><input type="checkbox" name="perm_bestleistungen" value="1"
                        <?= !empty($rolle['perm_bestleistungen']) ? 'checked' : '' ?>> Bestleistungen der Mitglieder
                    bearbeiten</label>
            </div>
            <div class="form-group" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                <label style="font-weight: bold; color: red;">
                    <input type="checkbox" name="perm_admin" value="1" <?= !empty($rolle['perm_admin']) ? 'checked' : '' ?>>
                    Vollzugriff (Admin) - Darf alles, inklusive Rollen & Benutzer
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Änderungen speichern</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>