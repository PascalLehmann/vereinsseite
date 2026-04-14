<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
if (empty($_SESSION['permissions']['admin']) && !in_array('admin', $_SESSION['roles'] ?? [])) {
    die("<div class='alert-error' style='margin: 20px;'>Zugriff verweigert.</div>");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <a href="erstellen.php" class="btn btn-secondary">Neuen Benutzer anlegen</a>
    </div>

    <h2>Benutzerverwaltung</h2>
    <p style="margin-bottom: 25px; color: #666;">Hier verwaltest du Systemzugänge und weist Benutzern ihre Rollen zu.
    </p>

    <div class="content-tile">
        <?php
        try {
            $stmtUsers = $pdo->query("
                SELECT u.id, u.username, GROUP_CONCAT(r.name SEPARATOR ', ') as rollen
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                GROUP BY u.id
                ORDER BY u.username ASC
            ");
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            if (count($users) > 0) {
                echo "<table class='admin-table'>";
                echo "<tr><th>ID</th><th>Benutzername</th><th>Rollen</th><th>Aktionen</th></tr>";
                foreach ($users as $u) {
                    echo "<tr>";
                    echo "<td>" . (int) $u['id'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($u['username']) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($u['rollen'] ?? '-') . "</td>";
                    echo "<td>";
                    echo "<a href='bearbeiten.php?id=" . $u['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                    echo "<a href='loeschen.php?id=" . $u['id'] . "' class='delete-link' title='Löschen' onclick='return confirm(\"Möchtest du User '" . htmlspecialchars($u['username']) . "' wirklich löschen?\");'><i class='fas fa-trash'></i></a>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (PDOException $e) {
            echo "Fehler: " . $e->getMessage();
        }
        ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>