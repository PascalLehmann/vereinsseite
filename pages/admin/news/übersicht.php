<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';
$pageTitle = "News Übersicht";
include_once '../../includes/header.php';

// SQL nutzt jetzt exakt 'bild_pfad'
$sql = "SELECT n.*, 
        (SELECT bild_pfad FROM news_bilder WHERE news_id = n.id ORDER BY id ASC LIMIT 1) as vorschaubild 
        FROM news n 
        ORDER BY n.datum DESC";
$news = $pdo->query($sql)->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>News Verwaltung</h1>
                <a href="erstellen.php" class="read-more"><i class="fa-solid fa-plus"></i> Neuer Beitrag</a>
            </div>

            <div class="news-card" style="padding:0; overflow: hidden;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#f4f7f6; border-bottom: 2px solid #eee;">
                            <th style="padding:15px; width:100px;">Vorschau</th>
                            <th style="padding:15px;">Titel / Datum</th>
                            <th style="padding:15px; text-align:center;">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $n): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px;">
                                    <div
                                        style="width:70px; height:50px; overflow:hidden; border-radius:8px; border: 1px solid #ddd; background:#eee;">
                                        <?php
                                        $raw = $n['vorschaubild'];
                                        // Pfad-Säuberung falls nötig
                                        $cleanName = str_replace('img/news/', '', $raw);
                                        $pfad = $raw ? "../../img/news/" . $cleanName : "../../img/news/default-news.jpg";
                                        ?>
                                        <img src="<?= $pfad ?>" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                </td>
                                <td style="padding:15px;">
                                    <strong><?= htmlspecialchars($n['titel']) ?></strong><br>
                                    <small style="color:#888;"><?= date("d.m.Y", strtotime($n['datum'])) ?></small>
                                </td>
                                <td style="padding:15px; text-align:center;">
                                    <div style="display: flex; justify-content: center; gap: 15px;">
                                        <a href="bearbeiten.php?id=<?= $n['id'] ?>" style="color:var(--secondary-blue);"><i
                                                class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="loeschen.php?id=<?= $n['id'] ?>" style="color:#e74c3c;"
                                            onclick="return confirm('Löschen?')"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</div>