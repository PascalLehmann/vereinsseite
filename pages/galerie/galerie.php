<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Bildergalerie";

// 1. DATENBANK & LAYOUT EINBINDEN
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main>
    <h2>Bildergalerie</h2>
    <p style="margin-bottom: 25px; color: #666;">Einige Eindrücke aus unserem Vereinsleben und den Spieltagen.</p>

    <div class="content-tile">
        <div class="news-gallery">
            <?php
            try {
                $stmt = $pdo->query("
                    SELECT k.id, k.name, 
                    (SELECT bild_pfad FROM galerie_bilder WHERE kategorie_id = k.id AND is_deleted = 0 ORDER BY hochgeladen_am DESC LIMIT 1) as cover_bild,
                    (SELECT COUNT(*) FROM galerie_bilder WHERE kategorie_id = k.id AND is_deleted = 0) as bild_anzahl
                    FROM galerie_kategorien k 
                    ORDER BY k.name ASC
                ");
                $kategorien = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($kategorien) > 0) {
                    foreach ($kategorien as $kat) {
                        echo "<a href='galerie-details.php?id=" . $kat['id'] . "' style='text-decoration:none; color:inherit;'>";
                        echo "<div class='content-tile' style='width:250px; padding:10px; text-align:center; transition:transform 0.3s;'>";
                        if ($kat['cover_bild']) {
                            echo "<img src='" . htmlspecialchars($kat['cover_bild']) . "' style='width:100%; height:150px; object-fit:cover; border-radius:6px; margin-bottom:10px;'>";
                        } else {
                            echo "<div style='width:100%; height:150px; background:#eee; border-radius:6px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:#999;'><i class='fas fa-folder-open fa-3x'></i></div>";
                        }
                        echo "<h3 style='margin:0; font-size:1.1rem;'>" . htmlspecialchars($kat['name']) . "</h3>";
                        echo "<small style='color:#666;'>" . $kat['bild_anzahl'] . " Bilder</small>";
                        echo "</div>";
                        echo "</a>";
                    }
                } else {
                    echo "<p>Aktuell sind noch keine Kategorien vorhanden.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='alert-error'>Fehler beim Laden der Galerie: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>