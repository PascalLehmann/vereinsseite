<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Mitglied bearbeiten";
include_once '../includes/header.php';

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
                <h1>Mitglied bearbeiten</h1>
                <a href="mitglieder-admin.php" class="read-more" style="background: #ccc; color: black; border: none;">Abbrechen</a>
            </div>

            <form action="mitglieder-update.php" method="POST" enctype="multipart/form-data" class="news-card">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">

                <h3 style="color: var(--secondary-blue); margin-bottom: 15px;">Profilbild</h3>
                <div style="display: flex; align-items: center; gap: 30px; margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 20px;">
                    
                    <div class="profile-preview-circle" style="width: 120px; height: 120px; margin: 0; background: #eee; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                        <?php 
                        $bildURL = getProfilbild($m['profilbild']); 
                        ?>
                        <img src="<?= $bildURL ?>" alt="Aktuelles Bild" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" style="width:100%; height:100%; object-fit:cover;">
                        <div style="display:none; flex-direction:column; align-items:center; color:#999;">
                            <i class="fa-solid fa-user-slash" style="font-size: 2rem;"></i>
                            <span style="font-size:0.6rem;">404</span>
                        </div>
                    </div>

                    <div style="flex: 1;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;">Profilbild ändern</label>
                        <input type="file" name="profilbild" accept="image/*" style="width: 100%; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 10px;">
                        <p style="font-size: 0.8rem; color: #666; margin-top: 8px;">
                            Aktueller Pfad: <code><?= $bildURL ?></code>
                        </p>
                    </div>
                </div>

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
                        <label><input type="checkbox" name="ist_gruendungsmitglied" value="1" <?= $m['ist_gruendungsmitglied'] ? 'checked' : '' ?>> Gründer</label>
                        <label><input type="checkbox" name="im_vorstand" value="1" <?= $m['im_vorstand'] ? 'checked' : '' ?> onchange="document.getElementById('vorstand-extra').style.display = this.checked ? 'block' : 'none'"> Im Vorstand</label>
                    </div>
                </div>

                <div id="vorstand-extra" style="display: <?= $m['im_vorstand'] ? 'block' : 'none' ?>; margin-bottom: 25px; background: #ebf5fb; padding: 20px; border-radius: 15px;">
                    <label>Vorstands-Rolle</label>
                    <input type="text" name="vorstands_rolle" value="<?= htmlspecialchars($m['vorstands_rolle']) ?>" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd;">
                </div>

                <h3 style="color: var(--secondary-blue); margin-bottom: 15px;">Bestleistungen</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background:#f9f9f9; padding:15px; border-radius:15px;">
                        <label>100 Würfe</label>
                        <input type="number" name="best_100_wert" value="<?= $m['best_100_wert'] ?>" style="width:100%; margin: 5px 0;">
                        <input type="date" name="best_100_datum" value="<?= $m['best_100_datum'] ?>" style="width:100%;">
                    </div>
                    <div style="background:#f9f9f9; padding:15px; border-radius:15px;">
                        <label>200 Würfe</label>
                        <input type="number" name="best_200_wert" value="<?= $m['best_200_wert'] ?>" style="width:100%; margin: 5px 0;">
                        <input type="date" name="best_200_datum" value="<?= $m['best_200_datum'] ?>" style="width:100%;">
                    </div>
                    <div style="background:#f9f9f9; padding:15px; border-radius:15px;">
                        <label>120 Würfe</label>
                        <input type="number" name="best_120_wert" value="<?= $m['best_120_wert'] ?>" style="width:100%; margin: 5px 0;">
                        <input type="date" name="best_120_datum" value="<?= $m['best_120_datum'] ?>" style="width:100%;">
                    </div>
                </div>

                <button type="submit" class="read-more" style="margin-top: 30px; background: var(--primary-orange); color: white; border: none; width: 100%;">Änderungen speichern</button>
            </form>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>