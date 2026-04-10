<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
include 'db.php'; // Lädt deine Zugangsdaten und die PDO-Verbindung
$pageTitle = "Aktuelle News"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
    <h1>Aktuelle News</h1>
    
    <?php
    $stmt = $pdo->query("SELECT * FROM news ORDER BY datum DESC");
    while ($row = $stmt->fetch()):
        $date = date("d.m.Y", strtotime($row['datum']));
    ?>
        <a href="news-details.php?id=<?= $row['id']; ?>" class="news-card-link">
            <article class="news-card-flex">
                <div class="news-content-left">
                    <small><i class="fa-regular fa-clock"></i> <?= $date; ?></small>
                    <h2><?= htmlspecialchars($row['titel']); ?></h2>
                    <p><?= mb_strimwidth(htmlspecialchars($row['inhalt']), 0, 120, "..."); ?></p>
                </div>

                <?php if ($row['bild'] && $row['bild'] != 'default.jpg'): ?>
                <div class="news-image-circle">
                    <img src="img/news/<?= $row['bild']; ?>" alt="News Bild">
                </div>
                <?php endif; ?>
            </article>
        </a>
    <?php endwhile; ?>
</main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>