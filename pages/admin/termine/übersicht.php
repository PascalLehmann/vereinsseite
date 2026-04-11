<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// 2. DATENBANK & LAYOUT EINBINDEN
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Termine verwalten</h2>

    <div class="action-bar">
        <a href="erstellen.php" class="btn btn-primary">+ Neuen Termin anlegen</a>
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
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                // Wir holen die Termine, die NEUESTEN bzw. KOMMENDEN zuerst
                // (Oder ORDER BY termin_datum DESC, je nachdem wie du es lieber magst)
                $sql = "SELECT t.id, t.typ, t.titel, t.veranstaltungsart, t.termin_datum, g.name AS gegner_name 
                        FROM termine t 
                        LEFT JOIN gegner g ON t.gegner_id = g.id 
                        ORDER BY t.termin_datum DESC";

                $stmt = $pdo->query($sql);
                $termine = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($termine) > 0) {
                    foreach ($termine as $row) {
                        echo "<tr>";

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

                        // 4. Aktionen (Buttons)
                        echo "<td>";
                        echo "<a href='bearbeiten.php?id=" . $row['id'] . "' class='action-link'>Bearbeiten</a>";
                        echo "<a href='loeschen.php?id=" . $row['id'] . "' class='delete-link' onclick='return confirm(\"Wirklich löschen?\");'>Löschen</a>";
                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Keine Termine vorhanden.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='4' class='alert-error'>Datenbank-Fehler: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

<?php
require_once __DIR__ . '/../../../templates/footer.php';
?>