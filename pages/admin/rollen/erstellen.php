<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Wächter: Login-Check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// 2. Wächter: Admin-Check
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin'])) {
    die("<div class='alert-error' style='margin: 20px;'>Zugriff verweigert: Du hast keine Administrator-Rechte.</div>");
}

// 3. Datenbank & Layout (3 Ebenen hoch)
require_once __DIR__ . '/../../../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $perm_admin = isset($_POST['perm_admin']) ? 1 : 0;

    if ($name !== '') {
        // Wenn Admin ausgewählt ist, werden alle Rechte automatisch vergeben.
        // Ansonsten werden die Werte aus den Checkboxen genommen.
        $perm_news_create = $perm_admin ? 1 : (isset($_POST['perm_news_create']) ? 1 : 0);
        $perm_news_edit = $perm_admin ? 1 : (isset($_POST['perm_news_edit']) ? 1 : 0);
        $perm_news_delete = $perm_admin ? 1 : (isset($_POST['perm_news_delete']) ? 1 : 0);
        $perm_news_delete_hard = $perm_admin ? 1 : (isset($_POST['perm_news_delete_hard']) ? 1 : 0);

        $perm_termine_create = $perm_admin ? 1 : (isset($_POST['perm_termine_create']) ? 1 : 0);
        $perm_termine_edit = $perm_admin ? 1 : (isset($_POST['perm_termine_edit']) ? 1 : 0);
        $perm_termine_delete = $perm_admin ? 1 : (isset($_POST['perm_termine_delete']) ? 1 : 0);
        $perm_termine_delete_hard = $perm_admin ? 1 : (isset($_POST['perm_termine_delete_hard']) ? 1 : 0);

        $perm_mitglieder_create = $perm_admin ? 1 : (isset($_POST['perm_mitglieder_create']) ? 1 : 0);
        $perm_mitglieder_edit = $perm_admin ? 1 : (isset($_POST['perm_mitglieder_edit']) ? 1 : 0);
        $perm_mitglieder_delete = $perm_admin ? 1 : (isset($_POST['perm_mitglieder_delete']) ? 1 : 0);
        $perm_mitglieder_bestleistungen = $perm_admin ? 1 : (isset($_POST['perm_mitglieder_bestleistungen']) ? 1 : 0);

        $perm_galerie_upload = $perm_admin ? 1 : (isset($_POST['perm_galerie_upload']) ? 1 : 0);
        $perm_galerie_delete = $perm_admin ? 1 : (isset($_POST['perm_galerie_delete']) ? 1 : 0);
        $perm_galerie_delete_hard = $perm_admin ? 1 : (isset($_POST['perm_galerie_delete_hard']) ? 1 : 0);
        $perm_galerie_kat_create = $perm_admin ? 1 : (isset($_POST['perm_galerie_kat_create']) ? 1 : 0);
        $perm_galerie_kat_delete = $perm_admin ? 1 : (isset($_POST['perm_galerie_kat_delete']) ? 1 : 0);
        $perm_galerie_kat_delete_hard = $perm_admin ? 1 : (isset($_POST['perm_galerie_kat_delete_hard']) ? 1 : 0);

        try {
            $stmt = $pdo->prepare("INSERT INTO roles (name, perm_news_create, perm_news_edit, perm_news_delete, perm_news_delete_hard, perm_termine_create, perm_termine_edit, perm_termine_delete, perm_termine_delete_hard, perm_mitglieder_create, perm_mitglieder_edit, perm_mitglieder_delete, perm_mitglieder_bestleistungen, perm_galerie_upload, perm_galerie_delete, perm_galerie_delete_hard, perm_galerie_kat_create, perm_galerie_kat_delete, perm_galerie_kat_delete_hard, perm_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $perm_news_create, $perm_news_edit, $perm_news_delete, $perm_news_delete_hard, $perm_termine_create, $perm_termine_edit, $perm_termine_delete, $perm_termine_delete_hard, $perm_mitglieder_create, $perm_mitglieder_edit, $perm_mitglieder_delete, $perm_mitglieder_bestleistungen, $perm_galerie_upload, $perm_galerie_delete, $perm_galerie_delete_hard, $perm_galerie_kat_create, $perm_galerie_kat_delete, $perm_galerie_kat_delete_hard, $perm_admin]);
            header("Location: uebersicht.php");
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Speichern: " . $e->getMessage();
        }
    } else {
        $error = "Bitte gib einen Rollennamen ein.";
    }
}

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <h2>Neue Rolle anlegen</h2>

    <div class="content-tile">
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="name">Rollenname:</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="z.B. vorstand">
            </div>

            <h3 style="margin-top: 20px; border-bottom: 2px solid var(--sidebar-color); padding-bottom: 5px;">
                Berechtigungen</h3>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #333;">📰 News</h4>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_news_create" value="1"> Erstellen</label>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_news_edit" value="1"> Bearbeiten</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox" name="perm_news_delete"
                            value="1"> Temporär löschen (Verstecken)</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox"
                            name="perm_news_delete_hard" value="1"> Endgültig löschen</label>
                </div>

                <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #333;">📅 Termine</h4>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_termine_create" value="1"> Erstellen</label>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_termine_edit" value="1"> Bearbeiten</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox"
                            name="perm_termine_delete" value="1"> Temporär löschen (Verstecken)</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox"
                            name="perm_termine_delete_hard" value="1"> Endgültig löschen</label>
                </div>

                <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #333;">👥 Mitglieder</h4>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_mitglieder_create" value="1"> Erstellen</label>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_mitglieder_edit" value="1"> Bearbeiten</label>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_mitglieder_delete" value="1"> Endgültig Löschen</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox"
                            name="perm_mitglieder_bestleistungen" value="1"> Nur Bestleistungen</label>
                </div>

                <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #333;">🖼️ Galerie</h4>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_galerie_upload" value="1"> Bilder hochladen</label>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_galerie_delete" value="1"> Temporär löschen (Bilder)</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox"
                            name="perm_galerie_delete_hard" value="1"> Endgültig löschen (Bilder)</label>
                </div>

                <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #333;">📁 Kategorien</h4>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_galerie_kat_create" value="1"> Anlegen</label>
                    <label style="display: block; font-weight: normal; margin-bottom: 5px;"><input type="checkbox"
                            name="perm_galerie_kat_delete" value="1"> Temporär löschen (Kategorien)</label>
                    <label style="display: block; font-weight: normal;"><input type="checkbox"
                            name="perm_galerie_kat_delete_hard" value="1"> Endgültig löschen (Kategorien)</label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                <label style="font-weight: bold; color: red;">
                    <input type="checkbox" name="perm_admin" value="1">
                    Vollzugriff (Admin) - Darf alles, inklusive Rollen & Benutzer
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Rolle speichern</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const adminCheckbox = document.querySelector('input[name="perm_admin"]');
        // Alle Checkboxen außer der Admin-Checkbox
        const allPermCheckboxes = document.querySelectorAll('input[type="checkbox"]:not([name="perm_admin"])');

        function togglePerms() {
            const isAdmin = adminCheckbox.checked;
            allPermCheckboxes.forEach(checkbox => {
                if (isAdmin) {
                    checkbox.checked = true;
                    checkbox.disabled = true; // Deaktivieren, um User-Interaktion zu verhindern
                } else {
                    checkbox.disabled = false; // Wieder aktivieren
                }
            });
        }

        adminCheckbox.addEventListener('change', togglePerms);
        togglePerms(); // Beim Laden der Seite ausführen, falls die Admin-Rolle bereits aktiv ist
    });
</script>