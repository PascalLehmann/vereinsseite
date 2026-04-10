<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Gegner-Verwaltung";
include_once '../includes/header.php';

// ... (Dein PHP Code zum Laden der Gegner) ...
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Gegner-Verwaltung</h1>
                <a href="gegner-create.php" class="read-more">Neuen Gegner anlegen</a>
            </div>

            <div class="news-card" style="padding:0;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f4f7f6;">
                            <th style="padding:15px; text-align:left;">Name</th>
                            <th style="padding:15px; text-align:left;">Adresse</th>
                            <th style="padding:15px; text-align:center;">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stmt = $pdo->query("SELECT * FROM gegner ORDER BY name ASC");
                        while($g = $stmt->fetch()): 
                        ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:15px;"><strong><?= htmlspecialchars($g['name']) ?></strong></td>
                            <td style="padding:15px;"><?= htmlspecialchars($g['strasse'].", ".$g['plz']." ".$g['ort']) ?></td>
                            <td style="padding:15px; text-align:center;">
                                <a href="gegner-edit.php?id=<?= $g['id'] ?>" style="margin-right:10px;"><i class="fa-solid fa-pen"></i></a>
                                <a href="gegner-delete.php?id=<?= $g['id'] ?>" style="color:red;" onclick="return confirm('Löschen?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div> </div> <?php include_once '../includes/footer.php'; ?> 