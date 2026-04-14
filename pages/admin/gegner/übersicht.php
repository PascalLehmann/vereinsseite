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
$pageTitle = "Gegner Verwaltung";
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Gegner verwalten</h2>

    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <a href="erstellen.php" class="btn btn-secondary">+ Neuen Gegner anlegen</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Verein</th>
                <th>Std. Spielzeit</th>
                <th>Bahnen</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM gegner ORDER BY name ASC");
                $gegner_liste = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($gegner_liste) > 0) {
                    foreach ($gegner_liste as $g) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($g['id']) . "</td>";
                        echo "<td><strong>" . htmlspecialchars($g['name']) . "</strong></td>";
                        echo "<td>" . (!empty($g['spielzeit']) ? date('H:i', strtotime($g['spielzeit'])) . ' Uhr' : '-') . "</td>";
                        echo "<td>" . htmlspecialchars($g['bahnen'] ?? '-') . "</td>";
                        echo "<td>";
                        echo "<a href='bearbeiten.php?id=" . $g['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                        echo "<a href='loeschen.php?id=" . $g['id'] . "' class='delete-link' title='Löschen' onclick='return confirm(\"Wirklich löschen?\");'><i class='fas fa-trash'></i></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center;'>Keine Gegner vorhanden.</td></tr>";
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