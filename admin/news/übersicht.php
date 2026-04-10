<?php
// 1. Authentifizierung und Datenbank
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

// 2. Titel für den Header setzen
$pageTitle = "News Übersicht";
include_once '../../includes/header.php';

// 3. SQL: Holt alle News und verknüpft das jeweils ERSTE Bild aus der Galerie als Vorschau
$sql = "SELECT n.*, 
        (SELECT dateiname FROM news_bilder WHERE news_id = n.id ORDER BY id ASC LIMIT 1) as vorschaubild 
        FROM news n 
        ORDER BY n.datum DESC";
$stmt = $pdo->query($sql);
$news = $stmt->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>

        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>News Verwaltung</h1>
                <a href="erstellen.php" class="read-more">
                    <i class="fa-solid fa-plus"></i> Neuen Beitrag erstellen
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 10px; margin-bottom: 20px;">
                    Beitrag erfolgreich gespeichert!
                </div>
            <?php endif; ?>

            <div class="news-card" style="padding:0; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f4f7f6; border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 15px; width: 100px;">Vorschau</th>
                            <th style="padding: 15px;">Titel & Datum</th>
                            <th style="padding: 15px; text-align: center; width: 150px;">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $n): ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.2s;"
                                onmouseover="this.style.background='#f9f9f9'"
                                onmouseout="this.style.background='transparent'">

                                <td style="padding: 10px;">
                                    <div
                                        style="width: 80px; height: 60px; overflow: hidden; border-radius: 8px; border: 1px solid #ddd; background: #eee; display: flex; align-items: center; justify-content: center;">
                                        <?php if (!empty($n['vorschaubild'])): ?>
                                            <img src="../../img/news/<?= htmlspecialchars($n['vorschaubild']) ?>"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fa-solid fa-image" style="color: #ccc; font-size: 1.5rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td style="padding: 15px;">
                                    <div
                                        style="font-weight: bold; color: var(--secondary-blue); font-size: 1.1rem; margin-bottom: 5px;">
                                        <?= htmlspecialchars($n['titel']) ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #888;">
                                        <i class="fa-regular fa-calendar-days"></i>
                                        <?= date("d.m.Y - H:i", strtotime($n['datum'])) ?> Uhr
                                    </div>
                                </td>

                                <td style="padding: 15px; text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 20px;">
                                        <a href="bearbeiten.php?id=<?= $n['id'] ?>"
                                            style="color: var(--secondary-blue); font-size: 1.2rem;" title="Bearbeiten">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="loeschen.php?id=<?= $n['id'] ?>" style="color: #e74c3c; font-size: 1.2rem;"
                                            title="Löschen"
                                            onclick="return confirm('Möchtest du diesen Beitrag inklusive aller Bilder wirklich löschen?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($news)): ?>
                            <tr>
                                <td colspan="3" style="padding: 50px; text-align: center; color: #999;">
                                    <i class="fa-solid fa-inbox"
                                        style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                    Noch keine News-Beiträge vorhanden.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div> <?php include_once '../../includes/footer.php'; ?>
</div>