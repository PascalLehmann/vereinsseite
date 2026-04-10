<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Mitglieder-Verwaltung";
include_once '../includes/header.php';

// Mitglieder laden
$stmt = $pdo->query("SELECT * FROM mitglieder ORDER BY nachname ASC");
$mitglieder = $stmt->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Mitglieder & Spieler</h1>
                <button onclick="document.getElementById('add-member-form').style.display='block'" class="read-more">+ Neues Mitglied</button>
            </div>

            <div id="add-member-form" class="news-card" style="display:none; margin-bottom: 40px; border-left: 5px solid var(--primary-orange);">
                <form action="mitglieder-save.php" method="POST" enctype="multipart/form-data">
                    <h3 style="color: var(--secondary-blue); margin-bottom: 15px;">Stammdaten & Profil</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label>Vorname</label>
                            <input type="text" name="vorname" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;">
                        </div>
                        <div>
                            <label>Nachname</label>
                            <input type="text" name="nachname" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;">
                        </div>
                        <div>
                            <label>Profilbild</label>
                            <input type="file" name="profilbild" accept="image/*" style="width:100%; padding:7px; background:#f0f0f0; border-radius:10px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label>Vereinseintritt</label>
                            <input type="date" name="eintrittsdatum" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 20px; padding-top: 25px;">
                            <label><input type="checkbox" name="ist_gruendungsmitglied" value="1"> Gründungsmitglied</label>
                            <label><input type="checkbox" name="im_vorstand" value="1" onchange="document.getElementById('vorstand-extra').style.display = this.checked ? 'block' : 'none'"> Im Vorstand</label>
                        </div>
                    </div>

                    <div id="vorstand-extra" style="display:none; margin-bottom: 20px; background: #ebf5fb; padding: 15px; border-radius: 10px;">
                        <label>Vorstands-Rolle</label>
                        <input type="text" name="vorstands_rolle" placeholder="z.B. 1. Vorsitzender, Sportwart..." style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;">
                    </div>

                    <h3 style="color: var(--secondary-blue); margin-bottom: 15px;">Bestleistungen (Kegeln)</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div style="background:#f9f9f9; padding:15px; border-radius:15px;">
                            <label><strong>100 Würfe</strong></label>
                            <input type="number" name="best_100_wert" placeholder="Holz" style="width:100%; margin-bottom:5px;">
                            <input type="date" name="best_100_datum" style="width:100%; margin-bottom:5px;">
                            <input type="text" name="best_100_ort" placeholder="Ort" style="width:100%;">
                        </div>
                        <div style="background:#f9f9f9; padding:15px; border-radius:15px;">
                            <label><strong>200 Würfe</strong></label>
                            <input type="number" name="best_200_wert" placeholder="Holz" style="width:100%; margin-bottom:5px;">
                            <input type="date" name="best_200_datum" style="width:100%; margin-bottom:5px;">
                            <input type="text" name="best_200_ort" placeholder="Ort" style="width:100%;">
                        </div>
                        <div style="background:#f9f9f9; padding:15px; border-radius:15px;">
                            <label><strong>120 Würfe</strong></label>
                            <input type="number" name="best_120_wert" placeholder="Holz" style="width:100%; margin-bottom:5px;">
                            <input type="date" name="best_120_datum" style="width:100%; margin-bottom:5px;">
                            <input type="text" name="best_120_ort" placeholder="Ort" style="width:100%;">
                        </div>
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="read-more" style="background:var(--secondary-blue); color:white; border:none;">Speichern</button>
                        <button type="button" onclick="document.getElementById('add-member-form').style.display='none'" class="read-more" style="background:#ccc; border:none; color:black;">Abbrechen</button>
                    </div>
                </form>
            </div>

            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--secondary-blue); color: white;">
                        <th style="padding:15px; border-radius: 15px 0 0 0;">Bild</th>
                        <th style="padding:15px;">Name</th>
                        <th style="padding:15px;">Status</th>
                        <th style="padding:15px; border-radius: 0 15px 0 0;">Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mitglieder as $m): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:10px;">
    <div class="profile-preview-circle" style="width:60px; height:60px; margin:0;">
        <img src="<?= getProfilbild($m['profilbild']) ?>" alt="Profilbild">
    </div>
</td>
                        <td style="padding:10px;"><strong><?= htmlspecialchars($m['vorname']." ".$m['nachname']) ?></strong></td>
                        <td style="padding:10px;">
                            <?= $m['ist_gruendungsmitglied'] ? '<span style="color:var(--primary-orange)">Gründer</span> ' : '' ?>
                            <?= $m['im_vorstand'] ? '<span style="color:blue">('.$m['vorstands_rolle'].')</span>' : 'Mitglied' ?>
                        </td>
                        <td style="padding:10px; text-align: center;">
                            <a href="mitglieder-edit.php?id=<?= $m['id'] ?>" style="color:var(--secondary-blue); margin-right:15px;">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
                            <a href="mitglieder-delete.php?id=<?= $m['id'] ?>" onclick="return confirm('Mitglied wirklich löschen?')" style="color:red;"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>