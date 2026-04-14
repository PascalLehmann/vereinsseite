<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
if (empty($_SESSION['permissions']['admin']) && !in_array('admin', $_SESSION['roles'] ?? [])) {
    die("<div class='alert-error' style='margin: 20px;'>Zugriff verweigert.</div>");
}

require_once __DIR__ . '/../../../db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("<div class='alert-error' style='margin: 20px;'>Benutzer nicht gefunden.</div>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $rollen = $_POST['rollen'] ?? [];

    try {
        $pdo->beginTransaction();

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmtPw = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmtPw->execute([$hash, $id]);
        }

        $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$id]);
        $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        foreach ($rollen as $rid) {
            $stmtRole->execute([$id, $rid]);
        }

        $pdo->commit();
        $success = "Änderungen gespeichert!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Fehler beim Speichern: " . $e->getMessage();
    }
}

$alleRollen = $pdo->query("SELECT * FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$aktuelleRollen = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
$aktuelleRollen->execute([$id]);
$aktuelleIds = $aktuelleRollen->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar"><a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a></div>
    <h2>Benutzer bearbeiten:
        <?= htmlspecialchars($user['username']) ?>
    </h2>

    <div class="content-tile">
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="color: green; margin-bottom: 15px; font-weight: bold;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width: 400px;">
            <div class="form-group">
                <label>Neues Passwort (leer lassen für keine Änderung):</label>
                <input type="password" name="password" class="form-control" autocomplete="new-password">
            </div>

            <h3 style="margin-top: 25px;">Rollen zuweisen</h3>
            <?php foreach ($alleRollen as $r): ?>
                <div class="form-group">
                    <label style="font-weight: normal;"><input type="checkbox" name="rollen[]" value="<?= $r['id'] ?>"
                            <?= in_array($r['id'], $aktuelleIds) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Speichern</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>