<?php
include 'db.php';

// 1. ID aus der URL holen und absichern
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Den entsprechenden News-Beitrag aus der Datenbank laden
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

// Falls keine News unter der ID gefunden wurde
if (!$news) {
    header("Location: news.php");
    exit;
}

$pageTitle = htmlspecialchars($news['titel']);
include 'includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <a href="news.php" class="read-more" style="margin-bottom: 25px;">
                <i class="fa-solid fa-arrow-left"></i> Zurück zur Übersicht
            </a>

            <article class="news-detail-view">
                <small style="color: #666; display: block; margin-bottom: 10px;">
                    <i class="fa-regular fa-clock"></i> <?= date("d.m.Y", strtotime($news['datum'])); ?>
                </small>
                
                <h1 style="margin-bottom: 20px;"><?= htmlspecialchars($news['titel']); ?></h1>

                <?php if ($news['bild'] && $news['bild'] != 'default.jpg'): ?>
                    <div style="width: 100%; max-height: 500px; overflow: hidden; border-radius: 20px; margin-bottom: 30px; box-shadow: var(--shadow-card);">
                        <img src="img/news/<?= $news['bild']; ?>" alt="Beitragsbild" style="width: 100%; height: auto; display: block;">
                    </div>
                <?php endif; ?>

                <div class="news-text" style="line-height: 1.8; font-size: 1.1rem; color: #333;">
                    <?= nl2br(htmlspecialchars($news['inhalt'])); ?>
                </div>
            </article>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>