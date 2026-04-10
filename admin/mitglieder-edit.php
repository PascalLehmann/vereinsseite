<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Mitglied bearbeiten";
include_once '../includes/header.php';

// Mitglieds-Daten laden
if (!isset($_GET['id'])) {
    header("Location: mitglieder-admin.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM mitglieder WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) {
    die("Mitglied nicht gefunden.");
}
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Mitglied bearbeiten: <?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></h1>
                <a href="mitglieder-admin.php" class="read-more" style="background: #ccc; color: black; border: none;">Zurück zur Liste</a>
            </div>

            <form action="mitglieder-update.php" method="POST" enctype="multipart/form-data" class="news-card" style="border-left: 5px solid var(--secondary-blue);">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">

                <h3 style="color: var(--secondary-blue); margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Profilbild</h3>
                <div style="display: flex; align-items: center; gap: 30px; margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 20px;">
                    <div class="profile-preview-circle" style="width: 120px; height: 120px; margin: 0; box-shadow: var(--shadow-card);">
                        <img src="<?= getProfilbild($m['profilbild']) ?>" alt="Aktuelles Bild">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;">Neues Bild hochladen</label>
                        <input type="file" name="profilbild" accept="image/*" style="width: 100%; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 10px;">
                        <p style="font-size: 0.8rem; color: #666; margin-top: 8px;">
                            <i class="fa-solid fa-circle-info"></i> Falls du kein neues Bild wählst, bleibt das aktuelle Bild erhalten.
                        </p>
                    </div>
                </div>

                <h3 style="color: var(--secondary-blue); margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Stammdaten</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label>Vorname</label>
                        <input type="text" name="vorname" value="<?= htmlspecialchars($m['vorname']) ?>" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd;">
                    </div>
                    <div>
                        <label>Nachname</label>
                        <input type="text" name="nachname" value="<?= htmlspecialchars($m['nachname']) ?>" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div>
                        <label>Vereinseintritt</label>
                        <input type="date" name="eintrittsdatum" value="<?= $m['eintrittsdatum'] ?>" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd;">
                    </div>
                    <div style="display: flex; align-items: center; gap: 25px; padding-top: 25px;">
                        <label style="cursor: pointer;"><input type="checkbox" name="ist_gruendungsmitglied" value="1" <?= $m['ist_gruendungsmitglied'] ? 'checked' : '' ?>> Gründungsmitglied</label>
                        <label style="cursor: pointer;"><input type="checkbox" name="im_vorstand" value="1" <?= $m['im_vorstand'] ? 'checked' : '' ?> onchange="document.getElementById('vorstand-extra-edit').style.display = this.checked ? 'block' : 'none'"> Im Vorstand</label>
                    </div>
                </div>

                <div id="vorstand-extra-edit" style="display: <?= $m['im_vorstand'] ? 'block' : 'none' ?>; margin-bottom: 25px; background: #ebf5fb; padding: 20px; border-radius: 15px; border-left: 5px solid #3498db;">
                    <label style="font-weight: bold; color: #2980b9;">Vorstands-Rolle</label>
                    <input type="text" name="vorstands_rolle" value="<?= htmlspecialchars($m['vorstands_rolle']) ?>" placeholder="z.B. Kassierer, Sportwart..." style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd; margin-top: 5px;">
                </div>

                <h3 style="color: var(--secondary-blue); margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Bestleistungen (Rekorde)</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    
                    <div style="background:#fdfefe; padding:15px; border-radius:15px; border: 1px solid #eee; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                        <label style="color: var(--primary-orange); font-weight: bold;">100 Würfe</label>
                        <input type="number" name="best_100_wert" value="<?= $m['best_100_wert'] ?>" placeholder="Ergebnis (Holz)" style="width:100%; padding:8px; margin: 10px 0;">
                        <input type="date" name="best_100_datum" value="<?= $m['best_100_datum'] ?>" style="width:100%; padding:8px; margin-bottom: 10px;">
                        <input type="text" name="best_100_ort" value="<?= htmlspecialchars($m['best_100_ort']) ?>" placeholder="Austragungsort" style="width:100%; padding:8px;">
                    </div>

                    <div style="background:#fdfefe; padding:15px; border-radius:15px; border: 1px solid #eee; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                        <label style="color: var(--primary-orange); font-weight: bold;">200 Würfe</label>
                        <input type="number" name="best_200_wert" value="<?= $m['best_200_wert'] ?>" placeholder="Ergebnis (Holz)" style="width:100%; padding:8px; margin: 10px 0;">
                        <input type="date" name="best_200_datum" value="<?= $m['best_200_datum'] ?>" style="width:100%; padding:8px; margin-bottom: 10px;">
                        <input type="text" name="best_200_ort" value="<?= htmlspecialchars($m['best_200_ort']) ?>" placeholder="Austragungsort" style="width:100%; padding:8px;">
                    </div>

                    <div style="background:#fdfefe; padding:15px; border-radius:15px; border: 1px solid #eee; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                        <label style="color: var(--primary-orange); font-weight: bold;">120 Würfe</label>
                        <input type="number" name="best_120_wert" value="<?= $m['best_120_wert'] ?>" placeholder="Ergebnis (Holz)" style="width:100%; padding:8px; margin: 10px 0;">
                        <input type="date" name="best_120_datum" value="<?= $m['best_120_datum'] ?>" style="width:100%; padding:8px; margin-bottom: 10px;">
                        <input type="text" name="best_120_ort" value="<?= htmlspecialchars($m['best_120_ort']) ?>" placeholder="Austragungsort" style="width:100%; padding:8px;">
                    </div>

                </div>

                <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; text-align: right;">
                    <button type="submit" class="read-more" style="background: var(--primary-orange); color: white; border: none; padding: 15px 40px; font-size: 1.1rem; cursor: pointer;">
                        <i class="fa-solid fa-floppy-disk"></i> Änderungen speichern
                    </button>
                </div>
            </form>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>