<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Wächter: Ist der User überhaupt eingeloggt?
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// 2. Wächter: Nur User mit der Rolle 'admin' dürfen hierher!
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles)) {
    die("<div class='alert-error' style='margin: 20px;'>Zugriff verweigert: Du hast keine Administrator-Rechte.</div>");
}

// 3. Datenbank & Layout einbinden (3 Ebenen nach oben)
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <a href="erstellen.php" class="btn btn-secondary">Neue Rolle anlegen</a>
    </div>

    <h2>Rollenverwaltung</h2>
    <p style="margin-bottom: 25px; color: #666;">Hier kannst du die Benutzerrollen und Berechtigungen im System
        verwalten.</p>

    <div class="content-tile">
        <?php
        try {
            // Alle verfügbaren Rollen aus der Datenbank abfragen
            $stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
            $db_rollen = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($db_rollen) > 0) {
                echo "<table class='admin-table'>";
                echo "<tr><th>ID</th><th>Rollenname</th><th>Zugeordnete Rechte</th><th>Aktionen</th></tr>";
                foreach ($db_rollen as $rolle) {

                    $rechte = [];
                    if (!empty($rolle['perm_admin']))
                        $rechte[] = "<span style='color:red; font-weight:bold;'>Admin (Alle)</span>";
                    if (!empty($rolle['perm_news']))
                        $rechte[] = "News";
                    if (!empty($rolle['perm_termine']))
                        $rechte[] = "Termine";
                    if (!empty($rolle['perm_bestleistungen']))
                        $rechte[] = "Bestleistungen";
                    $rechte_text = empty($rechte) ? "<em>Keine</em>" : implode(', ', $rechte);

                    echo "<tr>";
                    echo "<td>" . (int) $rolle['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($rolle['name']) . "</td>";
                    echo "<td>" . $rechte_text . "</td>";
                    echo "<td>";
                    echo "<a href='bearbeiten.php?id=" . $rolle['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                    echo "<a href='loeschen.php?id=" . $rolle['id'] . "' class='delete-link' title='Löschen' onclick='return confirm(\"Möchtest du die Rolle '" . htmlspecialchars($rolle['name']) . "' wirklich löschen?\");'><i class='fas fa-trash'></i></a>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Bisher sind keine Rollen im System angelegt.</p>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert-error'>Fehler beim Laden der Rollen: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>