<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Termin-Verwaltung";
include_once '../includes/header.php';

// Daten laden
$sql = "SELECT t.*, g.name AS gegner_name 
        FROM termine t 
        LEFT JOIN gegner g ON t.gegner_id = g.id 
        ORDER BY t.termin_datum DESC";
$stmt = $pdo->query($sql);
$termine = $stmt->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Termin-Verwaltung</h1>
                <a href="termine-create.php" class="read-more" style="background: var(--primary-orange); color: white; border: none;">
                    <i class="fa-solid fa-plus"></i> Neuen Termin anlegen
                </a>
            </div>

            <div class="news-card" style="padding: 0; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="background: #f4f7f6; text-align: left;">
                            <th style="padding: 15px;">Datum</th>
                            <th style="padding: 15px;">Typ</th>
                            <th style="padding: 15px;">Titel / Gegner</th>
                            <th style="padding: 15px; text-align: center;">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($termine as $t): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px;">
                                    <strong><?= date("d.m.Y", strtotime($t['termin_datum'])) ?></strong>
                                </td>
                                <td style="padding: 15px;">
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; background: <?= $t['typ'] == 'spiel' ? '#fdf2e9' : '#ebf5fb' ?>;">
                                        <?= $t['typ'] == 'spiel' ? 'Spiel' : 'Allgemein' ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <?= htmlspecialchars($t['typ'] == 'spiel' ? "vs. ".$t['gegner_name'] : $t['titel']) ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <a href="termine-edit.php?id=<?= $t['id'] ?>" style="margin-right:10px;"><i class="fa-solid fa-pen"></i></a>
                                    <a href="termine-delete.php?id=<?= $t['id'] ?>" style="color:red;" onclick="return confirm('Löschen?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div> </div> <?php include_once '../includes/footer.php'; ?>