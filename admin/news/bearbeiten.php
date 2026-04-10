<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$n = $stmt->fetch();
if (!$n) {
    header("Location: übersicht.php");
    exit;
}

$pageTitle = "News bearbeiten";
include_once '../../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>News bearbeiten</h1>
            <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="news-card">
                <input type="hidden" name="id" value="<?= $n['id'] ?>">

                <div style="margin-bottom:15px;">
                    <label>Titel</label>
                    <input type="text" name="titel" value="<?= htmlspecialchars($n['titel']) ?>" required>
                </div>

                <div style="margin-bottom:15px;">
                    <label>Inhalt</label>
                    <textarea name="inhalt" required
                        style="height: 200px;"><?= htmlspecialchars($n['inhalt']) ?></textarea>
                </div>

                <div style="margin-bottom:20px;">
                    <label>Bild ändern (Optional)</label>
                    <input type="file" name="bild">
                    <?php if ($n['bild']): ?>
                        <p><small>Aktuelles Bild: <?= $n['bild'] ?></small></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="read-more">Speichern</button>
                <a href="übersicht.php" style="margin-left:15px; color:gray;">Abbrechen</a>
            </form>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</div>