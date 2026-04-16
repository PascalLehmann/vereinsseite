<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$canGalerieUpload = !empty($perms['galerie_upload']);
$canGalerieDelete = !empty($perms['galerie_delete']);
$canGalerieDeleteHard = !empty($perms['galerie_delete_hard']);
$canGalerieKatCreate = !empty($perms['galerie_kat_create']);
$canGalerieKatDelete = !empty($perms['galerie_kat_delete']);

// Man muss mindestens ein Recht für die Galerie haben, um die Details zu sehen.
if (!$canGalerieUpload && !$canGalerieDelete && !$canGalerieDeleteHard && !$canGalerieKatCreate && !$canGalerieKatDelete) {
    die("Zugriff verweigert. Du benötigst mindestens ein Recht für die Galerie-Verwaltung.");
}

require_once __DIR__ . '/../../../db.php';

$kategorieId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$kategorieName = "Ohne Kategorie";

if ($kategorieId > 0) {
    $stmt = $pdo->prepare("SELECT name FROM galerie_kategorien WHERE id = ?");
    $stmt->execute([$kategorieId]);
    $kategorieName = $stmt->fetchColumn();

    if (!$kategorieName) {
        header("Location: uebersicht.php");
        exit;
    }
}

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Galerie</a>
    </div>

    <h2>Verwalten:
        <?= htmlspecialchars($kategorieName) ?>
    </h2>
    <p style="margin-bottom: 25px; color: #666;">Hier kannst du Bilder aus diesem Album löschen.</p>

    <div class="content-tile">
        <div class="news-gallery">
            <?php
            if ($kategorieId > 0) { // User mit dem Recht zum endgültigen Löschen sehen auch die versteckten Bilder
                if ($canGalerieDeleteHard) {
                    $stmt = $pdo->prepare("SELECT id, bild_pfad, is_deleted FROM galerie_bilder WHERE kategorie_id = ? ORDER BY hochgeladen_am DESC");
                } else {
                    $stmt = $pdo->prepare("SELECT id, bild_pfad, is_deleted FROM galerie_bilder WHERE kategorie_id = ? AND is_deleted = 0 ORDER BY hochgeladen_am DESC");
                }
                $stmt->execute([$kategorieId]);
            } else { // User mit dem Recht zum endgültigen Löschen sehen auch die versteckten Bilder
                if ($canGalerieDeleteHard) {
                    $stmt = $pdo->query("SELECT id, bild_pfad, is_deleted FROM galerie_bilder WHERE kategorie_id IS NULL ORDER BY hochgeladen_am DESC");
                } else {
                    $stmt = $pdo->query("SELECT id, bild_pfad, is_deleted FROM galerie_bilder WHERE kategorie_id IS NULL AND is_deleted = 0 ORDER BY hochgeladen_am DESC");
                }
            }
            $bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($bilder) > 0) {
                foreach ($bilder as $b) {
                    $opacity = $b['is_deleted'] ? 'opacity: 0.4; filter: grayscale(100%);' : '';
                    echo "<div style='position:relative; display:inline-block;'>";
                    echo "<img src='" . htmlspecialchars($b['bild_pfad']) . "' style='width:200px; height:150px; border-radius:8px; object-fit:cover; box-shadow: 0 4px 6px rgba(0,0,0,0.1); " . $opacity . "'>";

                    if ($b['is_deleted']) { // Nur wer endgültig löschen darf, darf auch wiederherstellen
                        if ($canGalerieDeleteHard) {
                            echo "<a href='wiederherstellen.php?id=" . $b['id'] . "&return_kat=" . $kategorieId . "' style='position:absolute; top:5px; right:45px; background:#2ecc71; color:white; padding:8px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);' title='Wiederherstellen'><i class='fas fa-undo'></i></a>";
                            echo "<a href='loeschen_endgueltig.php?id=" . $b['id'] . "&return_kat=" . $kategorieId . "' style='position:absolute; top:5px; right:5px; background:#c0392b; color:white; padding:8px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);' title='Endgültig Löschen' onclick='return confirm(\"Bild wirklich ENDGÜLTIG vom Server löschen?\");'><i class='fas fa-trash-alt'></i></a>";
                        }
                    } else if (!$b['is_deleted'] && $canGalerieDelete) {
                        echo "<a href='loeschen.php?id=" . $b['id'] . "&return_kat=" . $kategorieId . "' style='position:absolute; top:5px; right:5px; background:#e74c3c; color:white; padding:8px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);' title='Verstecken/Löschen' onclick='return confirm(\"Bild aus der Galerie entfernen?\");'><i class='fas fa-trash'></i></a>";
                    } else {
                        // User ohne Löschrechte sehen keine Buttons
                    }
                    echo "</div>";
                }
            } else {
                echo "<p>Keine Bilder in dieser Kategorie.</p>";
            }
            ?>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>