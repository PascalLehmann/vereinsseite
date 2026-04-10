<?php
// Nutze include_once, um Abstürze durch doppelte Einbindungen zu vermeiden
include_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prüfen, ob die ID gültig ist
if ($id <= 0) {
    header("Location: news.php");
    exit;
}

try {
    // News laden
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();

    if (!$news) {
        header("Location: news.php");
        exit;
    }
} catch (PDOException $e) {
    // Falls die Tabelle nicht existiert oder die Verbindung hakt
    die("Datenbankfehler: " . $e->getMessage());
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

                <div class="news-text" style="line-height: 1.8; margin-bottom: 40px;">
                    <?= $news['inhalt']; ?> 
                </div>

                <?php
                // Bilderstrecke sicher laden
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
                                        <a href="javascript:void(0)" onclick="openLightbox('img/news/<?= $bild['bild_pfad']; ?>')">
                                            <img src="img/news/<?= $bild['bild_pfad']; ?>" alt="News Bild">
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

    <?php include 'includes/footer.php'; ?>
</div>