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

if (!$canGalerieUpload && !$canGalerieDelete && !$canGalerieDeleteHard && !$canGalerieKatCreate && !$canGalerieKatDelete) {
    die("Zugriff verweigert. Du benötigst mindestens ein Recht für die Galerie-Verwaltung.");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <?php if ($canGalerieUpload): ?>
            <a href="hochladen.php" class="btn btn-secondary">+ Bilder hochladen</a>
        <?php endif; ?>
    </div>

    <h2>Galerie verwalten</h2>
    <p style="margin-bottom: 25px; color: #666;">Hier kannst du Bilder für die öffentliche Galerie hochladen oder
        löschen.</p>

    <div class="content-tile">
        <div class="news-gallery">
            <?php
            try {
                $stmt = $pdo->query("
                    SELECT k.id, k.name, 
                    (SELECT bild_pfad FROM galerie_bilder WHERE kategorie_id = k.id AND is_deleted = 0 ORDER BY hochgeladen_am DESC LIMIT 1) as cover_bild,
                    (SELECT COUNT(*) FROM galerie_bilder WHERE kategorie_id = k.id AND is_deleted = 0) as anzahl_live,
                    (SELECT COUNT(*) FROM galerie_bilder WHERE kategorie_id = k.id AND is_deleted = 1) as anzahl_versteckt
                    FROM galerie_kategorien k WHERE k.is_deleted = 0
                    ORDER BY k.name ASC
                ");
                $kategorien = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Bilder ohne Kategorie prüfen
                $stmtUncat = $pdo->query("
                    SELECT 
                        SUM(CASE WHEN is_deleted = 0 THEN 1 ELSE 0 END) as anzahl_live,
                        SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) as anzahl_versteckt 
                    FROM galerie_bilder WHERE kategorie_id IS NULL
                ");
                $uncatData = $stmtUncat->fetch(PDO::FETCH_ASSOC);
                $uncatLive = (int) ($uncatData['anzahl_live'] ?? 0);
                $uncatVersteckt = (int) ($uncatData['anzahl_versteckt'] ?? 0);
                $uncatTotal = $canGalerieDeleteHard ? ($uncatLive + $uncatVersteckt) : $uncatLive;

                // Kachel für "Ohne Kategorie" anzeigen
                if ($uncatTotal > 0) {
                    if ($canGalerieDeleteHard) {
                        $stmtUncatCover = $pdo->query("SELECT bild_pfad FROM galerie_bilder WHERE kategorie_id IS NULL ORDER BY hochgeladen_am DESC LIMIT 1");
                    } else {
                        $stmtUncatCover = $pdo->query("SELECT bild_pfad FROM galerie_bilder WHERE kategorie_id IS NULL AND is_deleted = 0 ORDER BY hochgeladen_am DESC LIMIT 1");
                    }
                    $uncatCover = $stmtUncatCover->fetchColumn();

                    echo "<a href='kategorie_details.php?id=0' style='text-decoration:none; color:inherit;'>";
                    echo "<div class='content-tile' style='width:250px; padding:10px; text-align:center; transition:transform 0.3s; margin-bottom:0;'>";
                    if ($uncatCover) {
                        echo "<img src='" . htmlspecialchars($uncatCover) . "' style='width:100%; height:150px; object-fit:cover; border-radius:6px; margin-bottom:10px;'>";
                    } else {
                        echo "<div style='width:100%; height:150px; background:#eee; border-radius:6px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:#999;'><i class='fas fa-folder-open fa-3x'></i></div>";
                    }
                    echo "<h3 style='margin:0; font-size:1.1rem;'>Ohne Kategorie</h3>";
                    echo "<small style='display:block; margin-top:5px; font-weight:bold;'>";
                    echo "<span style='color:#2ecc71;'><i class='fas fa-eye'></i> " . $uncatLive . "</span>";
                    if ($canGalerieDeleteHard)
                        echo " &nbsp;|&nbsp; <span style='color:#e74c3c;'><i class='fas fa-eye-slash'></i> " . $uncatVersteckt . "</span>";
                    echo "</small>";
                    echo "</div></a>";
                }

                // Alle anderen Kategorien anzeigen
                if (count($kategorien) > 0) {
                    foreach ($kategorien as $kat) {
                        echo "<a href='kategorie_details.php?id=" . $kat['id'] . "' style='text-decoration:none; color:inherit;'>";
                        echo "<div class='content-tile' style='width:250px; padding:10px; text-align:center; transition:transform 0.3s; margin-bottom:0;'>";
                        if ($kat['cover_bild']) {
                            echo "<img src='" . htmlspecialchars($kat['cover_bild']) . "' style='width:100%; height:150px; object-fit:cover; border-radius:6px; margin-bottom:10px;'>";
                        } else {
                            echo "<div style='width:100%; height:150px; background:#eee; border-radius:6px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:#999;'><i class='fas fa-folder-open fa-3x'></i></div>";
                        }
                        echo "<h3 style='margin:0; font-size:1.1rem;'>" . htmlspecialchars($kat['name']) . "</h3>";
                        echo "<small style='display:block; margin-top:5px; font-weight:bold;'>";
                        echo "<span style='color:#2ecc71;'><i class='fas fa-eye'></i> " . (int) $kat['anzahl_live'] . "</span>";
                        if ($canGalerieDeleteHard)
                            echo " &nbsp;|&nbsp; <span style='color:#e74c3c;'><i class='fas fa-eye-slash'></i> " . (int) $kat['anzahl_versteckt'] . "</span>";
                        echo "</small>";
                        echo "</div></a>";
                    }
                } else if ($uncatTotal == 0) {
                    echo "<p>Bisher sind keine Kategorien angelegt.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='alert-error'>Datenbank-Fehler: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>