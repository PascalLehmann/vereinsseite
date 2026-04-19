<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../../db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwort_neu = $_POST['passwort_neu'] ?? '';
    $passwort_wiederholung = $_POST['passwort_wiederholung'] ?? '';

    if (empty($passwort_neu) || empty($passwort_wiederholung)) {
        $error = 'Bitte fülle beide Felder aus.';
    } elseif ($passwort_neu !== $passwort_wiederholung) {
        $error = 'Die Passwörter stimmen nicht überein!';
    } elseif (strlen($passwort_neu) < 6) {
        $error = 'Das neue Passwort muss mindestens 6 Zeichen lang sein.';
    } else {
        // Passwort hashen und in der Datenbank aktualisieren
        $hash = password_hash($passwort_neu, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        if ($stmt->execute([':hash' => $hash, ':id' => $_SESSION['user_id']])) {
            $success = 'Dein Passwort wurde erfolgreich geändert!';
        } else {
            $error = 'Fehler beim Ändern des Passworts. Bitte versuche es später erneut.';
        }
    }
}

$pageTitle = "Passwort ändern";
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Passwort ändern</h2>
    <div class="action-bar" style="text-align: center;">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
    </div>

    <div class="content-tile" style="max-width: 500px;">
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div
                style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; text-align: center;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="passwort_aendern.php" method="POST">
            <div class="form-group">
                <label for="passwort_neu">Neues Passwort:</label>
                <input type="password" id="passwort_neu" name="passwort_neu" class="form-control" required
                    minlength="6">
            </div>
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="passwort_wiederholung">Neues Passwort wiederholen:</label>
                <input type="password" id="passwort_wiederholung" name="passwort_wiederholung" class="form-control"
                    required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;">
                <i class="fas fa-save"></i> Passwort speichern
            </button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>