<?php
include_once 'auth.php';
checkLogin();
include '../db.php';
$pageTitle = "News verwalten";
include '../includes/header.php';

$stmt = $pdo->query("SELECT * FROM news ORDER BY datum DESC");
$newsList = $stmt->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include '../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>News verwalten</h1>
                <a href="news-erstellen.php" class="read-more" style="background: var(--primary-orange); color: white;">+ Neue News</a>
            </div>

            <table style="width:100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; box-shadow: var(--shadow-card);">
                <thead style="background: var(--secondary-blue); color: white;">
                    <tr>
                        <th style="padding: 15px; text-align: left;">Datum</th>
                        <th style="padding: 15px; text-align: left;">Titel</th>
                        <th style="padding: 15px; text-align: center;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newsList as $row): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><?= date("d.m.Y", strtotime($row['datum'])); ?></td>
                        <td style="padding: 15px; font-weight: bold;"><?= htmlspecialchars($row['titel']); ?></td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="news-edit.php?id=<?= $row['id']; ?>" style="color: #3498db; margin-right: 15px;"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="news-delete.php?id=<?= $row['id']; ?>" style="color: #e74c3c;" onclick="return confirm('Wirklich löschen?');"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>