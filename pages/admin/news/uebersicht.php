<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG (RBAC - Wie ein #ifdef)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$isAdmin = !empty($perms['admin']);
$canNews = $isAdmin || !empty($perms['news']);

if (!$canNews) {
    die("Zugriff verweigert: Du hast keine Berechtigung für diese Seite.");
}

// 2. DATENBANK & LAYOUT EINBINDEN (3 Ebenen hoch ins Root!)
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>News verwalten</h2>

    <p style="margin-bottom: 20px;">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <a href="erstellen.php" class="btn btn-secondary">+ Neue News erstellen</a>
    </p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titel</th>
                <th>Datum</th>
                <th>Autor</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                if ($isAdmin) {
                    $sql = "SELECT n.id, n.titel, n.erstellt_am, n.is_deleted, u.username as autor_name FROM news n LEFT JOIN users u ON n.autor_id = u.id ORDER BY n.erstellt_am DESC";
                } else {
                    $sql = "SELECT n.id, n.titel, n.erstellt_am, n.is_deleted, u.username as autor_name FROM news n LEFT JOIN users u ON n.autor_id = u.id WHERE n.is_deleted = 0 ORDER BY n.erstellt_am DESC";
                }
                $stmt = $pdo->query($sql);
                $news_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($news_entries) > 0) {
                    foreach ($news_entries as $row) {
                        $rowStyle = $row['is_deleted'] ? "background-color: #f9f9f9; color: #999;" : "";
                        echo "<tr style='$rowStyle'>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['titel']) . "</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['erstellt_am'])) . "</td>";

                        $autor = !empty($row['autor_name']) ? htmlspecialchars($row['autor_name']) : 'Unbekannt';
                        echo "<td>" . $autor . "</td>";

                        echo "<td>";
                        if ($row['is_deleted']) {
                            echo "<span style='color: #e74c3c; font-weight: bold;'><i class='fas fa-eye-slash'></i> Versteckt</span>";
                        } else {
                            echo "<span style='color: #2ecc71; font-weight: bold;'><i class='fas fa-eye'></i> Live</span>";
                        }
                        echo "</td>";

                        echo "<td>";
                        echo "<a href='bearbeiten.php?id=" . $row['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                        if ($row['is_deleted'] && $isAdmin) {
                            echo "<a href='wiederherstellen.php?id=" . $row['id'] . "' class='action-link' title='Wiederherstellen' style='color: #2ecc71;'><i class='fas fa-undo'></i></a>";
                            echo "<a href='loeschen_endgueltig.php?id=" . $row['id'] . "' class='delete-link' title='Endgültig löschen' onclick='return confirm(\"Soll diese News wirklich ENDGÜLTIG gelöscht werden?\");'><i class='fas fa-trash-alt'></i></a>";
                        } else {
                            echo "<a href='loeschen.php?id=" . $row['id'] . "' class='delete-link' title='Verstecken / Löschen' onclick='return confirm(\"Wirklich löschen? (Wird für normale User unsichtbar)\");'><i class='fas fa-trash'></i></a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Keine News vorhanden.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='6' class='alert-error'>Datenbank-Fehler: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

<?php
require_once __DIR__ . '/../../../templates/footer.php';
?>