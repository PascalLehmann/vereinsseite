<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM gegner WHERE id = ?");
$stmt->execute([$id]);
$g = $stmt->fetch();

if (!$g) { header("Location: index.php"); exit; }

$pageTitle = "Gegner bearbeiten";
include_once '../../includes/header.php';
?>
<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>Gegner bearbeiten</h1>
            <form action="aktualisieren.php" method="POST" class="news-card">
                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                <div style="margin-bottom:15px;">
                    <label>Vereinsname</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($g['name']) ?>" required>
                </div>
                <div style="display:grid; grid-template-columns: 2fr 1fr 2fr; gap:15px; margin-bottom:15px;">
                    <div><label>Straße</label><input type="text" name="strasse" value="<?= htmlspecialchars($g['strasse']) ?>"></div>
                    <div><label>PLZ</label><input type="text" name="plz" value="<?= htmlspecialchars($g['plz']) ?>"></div>
                    <div><label>Ort</label><input type="text" name="ort" value="<?= htmlspecialchars($g['ort']) ?>"></div>
                </div>
                <button type="submit" class="read-more">Änderungen speichern</button>
                <a href="index.php" style="margin-left:15px; color:gray;">Abbrechen</a>
            </form>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</div>