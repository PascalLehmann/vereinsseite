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
$canBestleistungen = !empty($perms['bestleistungen']);

if (!$isAdmin && !$canBestleistungen) {
    die("Zugriff verweigert: Du hast keine Berechtigung für diese Seite.");
}

// 2. DATENBANK & LAYOUT EINBINDEN
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Mitglieder verwalten</h2>

    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <?php if ($isAdmin): ?>
            <a href="erstellen.php" class="btn btn-secondary">+ Neues Mitglied anlegen</a>
        <?php endif; ?>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 80px;">Bild</th>
                <th>Name & Status</th>
                <th style="width: 120px;">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM mitglieder ORDER BY nachname ASC");
                $mitglieder = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($mitglieder) > 0) {
                    foreach ($mitglieder as $m) {
                        echo "<tr>";

                        // Bild
                        echo "<td>";
                        echo "<div class='spieler-avatar' style='width:50px; height:50px; margin:0;'>";
                        $bildPfad = !empty($m['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($m['profilbild']) : '/assets/img/mitglieder/default-user.png';
                        echo "<img src='" . $bildPfad . "' alt='Profil'>";
                        echo "</div>";
                        echo "</td>";

                        // Name & Status
                        echo "<td>";
                        echo "<strong>" . htmlspecialchars($m['vorname'] . " " . $m['nachname']) . "</strong>";
                        echo "<div style='margin-top: 5px; font-size: 0.85rem; display: flex; gap: 10px;'>";
                        if ($m['im_vorstand']) {
                            echo "<span style='color: #e67e22; font-weight: bold;'><i class='fa-solid fa-star'></i> " . htmlspecialchars($m['vorstands_rolle']) . "</span>";
                        }
                        if (!empty($m['ist_gruendungsmitglied'])) {
                            echo "<span style='color: #2980b9; font-weight: bold;'><i class='fa-solid fa-certificate'></i> Gründer</span>";
                        }
                        echo "</div>";
                        echo "</td>";

                        // Aktionen
                        echo "<td>";
                        echo "<a href='bearbeiten.php?id=" . $m['id'] . "' class='action-link' title='Bearbeiten'><i class='fas fa-edit'></i></a>";
                        if ($isAdmin) {
                            echo "<a href='loeschen.php?id=" . $m['id'] . "' class='delete-link' title='Löschen' onclick='return confirm(\"Wirklich löschen?\");'><i class='fas fa-trash'></i></a>";
                        }
                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align: center;'>Keine Mitglieder vorhanden.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='3' class='alert-error'>Datenbank-Fehler: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>