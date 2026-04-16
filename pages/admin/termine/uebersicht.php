<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$isAdmin = !empty($perms['admin']);
$canTermineCreate = $isAdmin || !empty($perms['termine_create']);
$canTermineEdit = $isAdmin || !empty($perms['termine_edit']);
$canTermineDelete = $isAdmin || !empty($perms['termine_delete']);

if (!$canTermineCreate && !$canTermineEdit && !$canTermineDelete && !$isAdmin) {
    die("Zugriff verweigert: Du hast keine Berechtigung für diese Seite.");
}

// 2. DATENBANK & LAYOUT EINBINDEN
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Termine verwalten</h2>

    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <?php if ($canTermineCreate): ?>
            <a href="erstellen.php" class="btn btn-secondary">+ Neuen Termin anlegen</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green; font-weight: bold; margin-bottom: 15px;">Aktion erfolgreich durchgeführt!</p>
    <?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Typ</th>
                <th>Titel / Gegner</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                if ($isAdmin) {
                    $sql = "SELECT t.id, t.typ, t.titel, t.veranstaltungsart, t.termin_datum, t.is_deleted, g.name AS gegner_name 
                            FROM termine t LEFT JOIN gegner g ON t.gegner_id = g.id 
                            ORDER BY t.termin_datum DESC";
                } else {
                    $sql = "SELECT t.id, t.typ, t.titel, t.veranstaltungsart, t.termin_datum, t.is_deleted, g.name AS gegner_name 
                            FROM termine t LEFT JOIN gegner g ON t.gegner_id = g.id 
                            WHERE t.is_deleted = 0 ORDER BY t.termin_datum DESC";
                }

                $stmt = $pdo->query($sql);
                $termine = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($termine) > 0) {
                    foreach ($termine as $row) {
                        $rowStyle = $row['is_deleted'] ? "background-color: #f9f9f9; color: #999;" : "";
                        echo "<tr style='$rowStyle'>";

                        // 1. Datum formatieren
                        $datum = $row['termin_datum'] ? date('d.m.Y', strtotime($row['termin_datum'])) : 'Kein Datum';
                        echo "<td>" . $datum . "</td>";

                        // 2. Typ (Spiel oder Event) mit Farbe
                        if ($row['typ'] === 'spiel') {
                            echo "<td style='color: #e67e22; font-weight: bold;'>Spiel</td>";
                        } else {
                            echo "<td style='color: #3498db; font-weight: bold;'>Event</td>";
                        }

                        // 3. Titel (Logik: Wenn Spiel, zeige Gegner, sonst Titel/Art)
                        $anzeige_titel = "";
                        if ($row['typ'] === 'spiel' && !empty($row['gegner_name'])) {
                            $anzeige_titel = "vs. " . htmlspecialchars($row['gegner_name']);
                        } else {
                            $anzeige_titel = htmlspecialchars($row['titel'] ?: $row['veranstaltungsart']);
                        }
                        echo "<td>" . $anzeige_titel . "</td>";

                        // Status
                        echo "<td>";
                        if ($row['is_deleted']) {
                            echo "<span style='color: #e74c3c; font-weight: bold;'><i class='fas fa-eye-slash'></i> Versteckt</span>";
                        } else {
                            echo "<span style='color: #2ecc71; font-weight: bold;'><i class='fas fa-eye'></i> Live</span>";
                        }
                        echo "</td>";

                        // 4. Aktionen (Buttons)
                        echo "<td>";
                        if ($canTermineEdit) {
                            echo "<a href='bearbeiten.php?id=" . $row['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                        }
                        if ($row['is_deleted'] && $isAdmin) {
                            echo "<a href='wiederherstellen.php?id=" . $row['id'] . "' class='action-link' title='Wiederherstellen' style='color: #2ecc71;'><i class='fas fa-undo'></i></a>";
                            echo "<a href='loeschen_endgueltig.php?id=" . $row['id'] . "' class='delete-link' title='Endgültig löschen' onclick='return confirm(\"Soll dieser Termin wirklich ENDGÜLTIG gelöscht werden?\");'><i class='fas fa-trash-alt'></i></a>";
                        } elseif (!$row['is_deleted'] && $canTermineDelete) {
                            echo "<a href='loeschen.php?id=" . $row['id'] . "' class='delete-link' title='Verstecken / Löschen' onclick='return confirm(\"Wirklich löschen? (Wird für normale User unsichtbar)\");'><i class='fas fa-trash'></i></a>";
                        }
                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center;'>Keine Termine vorhanden.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='5' class='alert-error'>Datenbank-Fehler: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

<?php
require_once __DIR__ . '/../../../templates/footer.php';
?>