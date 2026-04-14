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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rollen = $_POST['rollen'] ?? [];

    if ($username !== '' && $password !== '') {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $userId = $pdo->lastInsertId();

            // Rollen zuweisen
            $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            foreach ($rollen as $rid) {
                $stmtRole->execute([$userId, $rid]);
            }
            header("Location: uebersicht.php");
            exit;
        } catch (PDOException $e) {
            $error = "Fehler: Benutzername evtl. schon vergeben.";
        }
    } else {
        $error = "Benutzername und Passwort sind Pflicht!";
    }
}

$alleRollen = $pdo->query("SELECT * FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar"><a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a></div>
    <h2>Neuen Benutzer anlegen</h2>

    <div class="content-tile">
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width: 400px;">
            <div class="form-group">
                <label>Benutzername:</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Passwort:</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password">
            </div>

            <h3 style="margin-top: 25px;">Rollen zuweisen</h3>
            <?php foreach ($alleRollen as $r): ?>
                <div class="form-group">
                    <label style="font-weight: normal;"><input type="checkbox" name="rollen[]" value="<?= $r['id'] ?>">
                        <?= htmlspecialchars($r['name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Benutzer anlegen</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>