<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';
$pageTitle = "Mitglieder Übersicht";
include_once '../../includes/header.php';

// Mitglieder laden
$stmt = $pdo->query("SELECT * FROM mitglieder ORDER BY nachname ASC");
$mitglieder = $stmt->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>

        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Mitglieder Verwaltung</h1>
                <a href="erstellen.php" class="read-more">
                    <i class="fa-solid fa-plus"></i> Neues Mitglied
                </a>
            </div>

            <div class="news-card" style="padding:0; overflow: hidden;">
                <table>
                    <thead>
                        <tr style="background:#f4f7f6; border-bottom: 2px solid #eee;">
                            <th style="padding:15px; text-align:left; width: 80px;">Bild</th>
                            <th style="padding:15px; text-align:left;">Name & Status</th>
                            <th style="padding:15px; text-align:center; width: 120px;">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mitglieder as $m): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px;">
                                    <div class="news-image-circle" style="width:60px; height:60px; min-width:60px;">
                                        <?php
                                        $bild = !empty($m['profilbild']) ? "../../img/mitglieder/" . $m['profilbild'] : "../../img/mitglieder/default-user.png";
                                        ?>
                                        <img src="<?= $bild ?>" alt="Profil">
                                    </div>
                                </td>

                                <td style="padding:15px;">
                                    <strong style="font-size: 1.1rem; color: var(--secondary-blue);">
                                        <?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?>
                                    </strong>
                                    <div style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                                        <?php if ($m['im_vorstand']): ?>
                                            <span
                                                style="background: #fdf2e9; color: #e67e22; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; border: 1px solid #e67e22; font-weight: bold;">
                                                <i class="fa-solid fa-star"></i> <?= htmlspecialchars($m['vorstands_rolle']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (isset($m['ist_gruendungsmitglied']) && $m['ist_gruendungsmitglied']): ?>
                                            <span
                                                style="background: #ebf5fb; color: #2980b9; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; border: 1px solid #2980b9; font-weight: bold;">
                                                <i class="fa-solid fa-certificate"></i> Gründer
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td style="padding:15px; text-align:center;">
                                    <div style="display: flex; justify-content: center; gap: 20px;">
                                        <a href="bearbeiten.php?id=<?= $m['id'] ?>"
                                            style="color: var(--secondary-blue); font-size: 1.2rem;" title="Bearbeiten">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="loeschen.php?id=<?= $m['id'] ?>" style="color: #e74c3c; font-size: 1.2rem;"
                                            title="Löschen"
                                            onclick="return confirm('Möchtest du <?= htmlspecialchars($m['vorname']) ?> wirklich unwiderruflich löschen?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($mitglieder)): ?>
                            <tr>
                                <td colspan="3" style="padding: 40px; text-align: center; color: #999;">
                                    Keine Mitglieder gefunden.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div> <?php include_once '../../includes/footer.php'; ?>
</div>