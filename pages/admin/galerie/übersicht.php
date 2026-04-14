<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin']) && empty($perms['news'])) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <a href="hochladen.php" class="btn btn-primary">+ Bilder hochladen</a>
    </div>

    <h2>Galerie verwalten</h2>
    <p style="margin-bottom: 25px; color: #666;">Hier kannst du Bilder für die öffentliche Galerie hochladen oder
        löschen.</p>

    <div class="content-tile">
        <div class="news-gallery">
            <?php
            try {
                $stmt = $pdo->query("SELECT id, bild_pfad FROM galerie_bilder ORDER BY hochgeladen_am DESC");
                $bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($bilder) > 0) {
                    foreach ($bilder as $b) {
                        echo "<div style='position:relative; display:inline-block;'>";
                        echo "<img src='" . htmlspecialchars($b['bild_pfad']) . "' style='width:200px; height:150px; border-radius:8px; object-fit:cover; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
                        echo "<a href='loeschen.php?id=" . $b['id'] . "' style='position:absolute; top:5px; right:5px; background:#e74c3c; color:white; padding:8px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);' title='Löschen' onclick='return confirm(\"Dieses Bild wirklich aus der Galerie löschen?\");'><i class='fas fa-trash'></i></a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Bisher sind keine Bilder in der Galerie.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='alert-error'>Datenbank-Fehler: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>