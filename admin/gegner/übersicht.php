<?php
include_once '../auth.php'; 
checkLogin();
include_once '../../db.php';
$pageTitle = "Gegner Verwaltung";
include_once '../../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Gegner Verwaltung</h1>
                <a href="erstellen.php" class="read-more">Neuer Gegner</a>
            </div>

            <div class="news-card" style="padding:0; overflow: hidden;">
                <table>
                    <thead>
                        <tr style="background:#f4f7f6;">
                            <th style="padding:15px; text-align:left;">Verein</th>
                            <th style="padding:15px; text-align:center;">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stmt = $pdo->query("SELECT * FROM gegner ORDER BY name ASC");
                        while($g = $stmt->fetch()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:15px;"><strong><?= htmlspecialchars($g['name']) ?></strong></td>
                            <td style="padding:15px; text-align:center;">
                                <a href="bearbeiten.php?id=<?= $g['id'] ?>" style="margin-right:15px; color:#3498db;"><i class="fa-solid fa-pen"></i></a>
                                <a href="loeschen.php?id=<?= $g['id'] ?>" style="color:#e74c3c;" onclick="return confirm('Löschen?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div> <?php include_once '../../includes/footer.php'; ?> </div>
