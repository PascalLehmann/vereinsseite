<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB einbinden
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$pageTitle = "Aktuelle News";

// Header lädt automatisch Navigation
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>Aktuelle News</h1>

            <?php
            // News + erstes Bild laden
            $sql = "SELECT n.*, 
                    (SELECT bild_pfad FROM news_bilder WHERE news_id = n.id LIMIT 1) AS vorschau_bild
                    FROM news n 
                    ORDER BY n.datum DESC";

            $stmt = $pdo->query($sql);

            while ($row = $stmt->fetch()):
                $date = date("d.m.Y", strtotime($row['datum']));
                ?>

                <a href="/pages/news/news-details.php?id=<?= $row['id']; ?>" class="news-card-link">
                    <article class="news-card-flex">

                        <div class="news-content-left">
                            <small><i class="fa-regular fa-clock"></i> <?= $date; ?></small>
                            <h2><?= htmlspecialchars($row['titel']); ?></h2>
                            <p><?= mb_strimwidth(strip_tags($row['inhalt']), 0, 120, "..."); ?></p>
                        </div>

                        <div class="news-image-circle">
                            <?php if ($row['vorschau_bild']): ?>
                                <img src="/assets/img/news/<?= $row['vorschau_bild']; ?>" alt="News Bild">
                            <?php else: ?>
                                <img src="/assets/img/default_news.jpg" alt="Standard Bild">
                            <?php endif; ?>
                        </div>

                    </article>
                </a>

            <?php endwhile; ?>

        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>