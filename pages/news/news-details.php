<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB laden
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// ID prüfen
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: /pages/news/news.php");
    exit;
}

// News laden
try {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();

    if (!$news) {
        header("Location: /pages/news/news.php");
        exit;
    }
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}

$pageTitle = htmlspecialchars($news['titel']);
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">

            <a href="/pages/news/news.php" class="read-more" style="margin-bottom: 25px;">
                <i class="fa-solid fa-arrow-left"></i> Zurück zur Übersicht
            </a>

            <article class="news-detail-view">

                <small style="color: #666; display: block; margin-bottom: 10px;">
                    <i class="fa-regular fa-clock"></i> <?= date("d.m.Y", strtotime($news['datum'])); ?>
                </small>

                <h1 style="margin-bottom: 20px;"><?= htmlspecialchars($news['titel']); ?></h1>

                <div class="news-text" style="line-height: 1.8; margin-bottom: 40px;">
                    <?= $news['inhalt']; ?>
                </div>

                <?php
                // Bilderstrecke laden
                try {
                    $stmtB = $pdo->prepare("SELECT * FROM news_bilder WHERE news_id = ?");
                    $stmtB->execute([$id]);
                    $bilder = $stmtB->fetchAll();

                    if ($bilder): ?>
                        <h3>Bilderstrecke</h3>
                        <div class="photo-grid">
                            <?php foreach ($bilder as $bild): ?>
                                <div class="photo-card">
                                    <div class="photo-box">
                                        <a href="javascript:void(0)"
                                            onclick="openLightbox('/assets/img/news/<?= $bild['bild_pfad']; ?>')">
                                            <img src="/assets/img/news/<?= $bild['bild_pfad']; ?>" alt="News Bild">
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif;

                } catch (PDOException $e) {
                    echo "<p>Bildergalerie konnte nicht geladen werden.</p>";
                }
                ?>

            </article>

        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>