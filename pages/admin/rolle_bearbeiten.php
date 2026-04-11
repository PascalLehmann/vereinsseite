<?php
session_start();
require_once __DIR__ . '/../../db.php';

if (!in_array('admin', $_SESSION['rollen'])) {
    die("Kein Zugriff.");
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$id]);
$rolle = $stmt->fetch();

if (!$rolle)
    die("Rolle nicht gefunden.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $pdo->prepare("UPDATE roles SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: rollen.php");
        exit;
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rolle bearbeiten</title>
</head>

<body>

    <h1>Rolle bearbeiten</h1>

    <form method="post">
        <label>Rollenname:
            <input type="text" name="name" value="<?= htmlspecialchars($rolle['name']) ?>" required>
        </label><br><br>

        <button type="submit">Speichern</button>
    </form>

</body>

</html>