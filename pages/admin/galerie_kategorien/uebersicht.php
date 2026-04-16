<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$canKatCreate = !empty($perms['galerie_kat_create']);
$canKatDelete = !empty($perms['galerie_kat_delete']);
$canKatDeleteHard = !empty($perms['galerie_kat_delete_hard']);

if (!$canKatCreate && !$canKatDelete && !$canKatDeleteHard) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <?php if ($canKatCreate): ?>
            <a href="erstellen.php" class="btn btn-primary">+ Neue Kategorie anlegen</a>
        <?php endif; ?>
    </div>

    <h2>Galerie Kategorien</h2>
    <p style="margin-bottom: 25px; color: #666;">Hier verwaltest du die Alben/Kategorien für deine Galerie.</p>

    <div class="content-tile">
        <?php
        try {
            // Nur User mit dem Recht zum endgültigen Löschen sehen auch die versteckten Kategorien
            if ($canKatDeleteHard) {
                $stmt = $pdo->query("SELECT * FROM galerie_kategorien ORDER BY name ASC");
            } else {
                $stmt = $pdo->query("SELECT * FROM galerie_kategorien WHERE is_deleted = 0 ORDER BY name ASC");
            }
            $kategorien = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($kategorien) > 0) {
                echo "<table class='admin-table'>";
                echo "<tr><th>ID</th><th>Kategoriename</th><th>Status</th><th>Aktionen</th></tr>";
                foreach ($kategorien as $kat) {
                    $rowStyle = !empty($kat['is_deleted']) ? "background-color: #f9f9f9; color: #999;" : "";
                    echo "<tr style='$rowStyle'>";
                    echo "<td>" . (int) $kat['id'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($kat['name']) . "</strong></td>";
                    echo "<td>";
                    if (!empty($kat['is_deleted'])) {
                        echo "<span style='color: #e74c3c; font-weight: bold;'><i class='fas fa-eye-slash'></i> Versteckt</span>";
                    } else {
                        echo "<span style='color: #2ecc71; font-weight: bold;'><i class='fas fa-eye'></i> Live</span>";
                    }
                    echo "</td>";
                    echo "<td>";
                    if (!empty($kat['is_deleted'])) { // Nur wer endgültig löschen darf, darf auch wiederherstellen
                        if ($canKatDeleteHard) {
                            echo "<a href='wiederherstellen.php?id=" . $kat['id'] . "' class='action-link' title='Wiederherstellen' style='color: #2ecc71;'><i class='fas fa-undo'></i></a>";
                            echo "<a href='loeschen_endgueltig.php?id=" . $kat['id'] . "' class='delete-link' title='Endgültig löschen' onclick='return confirm(\"Kategorie ENDGÜLTIG löschen? Zugehörige Bilder werden nicht gelöscht, aber die Zuordnung geht verloren.\");'><i class='fas fa-trash-alt'></i></a>";
                        }
                    } else {
                        if ($canKatCreate)
                            echo "<a href='bearbeiten.php?id=" . $kat['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                        if ($canKatDelete)
                            echo "<a href='loeschen.php?id=" . $kat['id'] . "' class='delete-link' title='Verstecken' onclick='return confirm(\"Kategorie verstecken? Sie wird auf der Webseite nicht mehr angezeigt.\");'><i class='fas fa-trash'></i></a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Bisher sind keine Kategorien angelegt.</p>";
            }
        } catch (PDOException $e) {
            echo "Fehler: " . $e->getMessage();
        }
        ?>
    </div>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>