<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG (RBAC - Wie ein #ifdef)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert: Du hast nicht die nötigen Rechte für diese Seite.");
}

// 2. DATENBANK & LAYOUT EINBINDEN (3 Ebenen hoch ins Root!)
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>News verwalten</h2>

    <p style="margin-bottom: 20px;">
        <a href="erstellen.php" class="btn btn-secondary">+ Neue News erstellen</a>
    </p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titel</th>
                <th>Datum</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $sql = "SELECT id, titel, erstellt_am FROM news ORDER BY erstellt_am DESC";
                $stmt = $pdo->query($sql);
                $news_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($news_entries) > 0) {
                    foreach ($news_entries as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['titel']) . "</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['erstellt_am'])) . "</td>";
                        echo "<td>";
                        echo "<a href='bearbeiten.php?id=" . $row['id'] . "' class='action-link'>Bearbeiten</a>";
                        echo "<a href='loeschen.php?id=" . $row['id'] . "' class='delete-link' onclick='return confirm(\"Wirklich löschen?\");'>Löschen</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Keine News vorhanden.</td></tr>";
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