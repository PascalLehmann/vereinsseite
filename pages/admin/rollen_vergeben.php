<?php
session_start();
require_once __DIR__ . '/../../db.php';

if (!in_array('admin', $_SESSION['rollen'])) {
    die("Kein Zugriff.");
}

$userId = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user)
    die("User nicht gefunden.");

$alleRollen = $pdo->query("SELECT * FROM roles")->fetchAll();

$stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
$stmt->execute([$userId]);
$aktuelle = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $neue = $_POST['rollen'] ?? [];

    $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);

    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    foreach ($neue as $rid) {
        $stmt->execute([$userId, $rid]);
    }

    header("Location: rollen_vergeben_liste.php");
    exit;
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rollen vergeben</title>
</head>

<body>

    <h1>Rollen für <?= htmlspecialchars($user['username']) ?></h1>

    <form method="post">
        <?php foreach ($alleRollen as $r): ?>
            <label>
                <input type="checkbox" name="rollen[]" value="<?= $r['id'] ?>" <?= in_array($r['id'], $aktuelle) ? 'checked' : '' ?>>
                <?= htmlspecialchars($r['name']) ?>
            </label><br>
        <?php endforeach; ?>

        <br>
        <button type="submit">Speichern</button>
    </form>

</body>

</html>