<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['admin'])) {
    die("Zugriff verweigert: Du hast keine Administrator-Rechte.");
}

require_once __DIR__ . '/../../db.php';

// Tabelle für Systemeinstellungen erstellen, falls sie noch nicht existiert
$pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
)");

// Hilfsfunktion für die API-Kommunikation
function fetchSportwinnerAPI($params)
{
    $apiUrl = 'https://blbk.sportwinner.de/php/blbk/service.php';
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                "Accept: application/json, text/javascript, */*; q=0.01\r\n" .
                "X-Requested-With: XMLHttpRequest\r\n" .
                "Referer: https://blbk.sportwinner.de/\r\n" .
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
            'content' => http_build_query($params)
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];
    $context = stream_context_create($options);
    $json = @file_get_contents($apiUrl, false, $context);
    return $json ? json_decode($json, true) : [];
}

$success = '';
$error = '';

// Speichern der Liga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tabelle'])) {
    $saison_id = $_POST['saison_id'] ?? '';
    $liga_id = $_POST['liga_id'] ?? '';

    if (!empty($saison_id) && !empty($liga_id)) {
        $stmt = $pdo->prepare("REPLACE INTO system_settings (setting_key, setting_value) VALUES ('sportwinner_saison_id', ?), ('sportwinner_liga_id', ?)");
        $stmt->execute([$saison_id, $liga_id]);
        $success = "Tabelle erfolgreich konfiguriert!";
    } else {
        $error = "Bitte wähle Saison und Liga aus.";
    }
}

// Aktuelle Werte aus der Datenbank laden
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('sportwinner_saison_id', 'sportwinner_liga_id')");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$saved_saison_id = $settings['sportwinner_saison_id'] ?? '';
$saved_liga_id = $settings['sportwinner_liga_id'] ?? '';

// Für das Formular: Wenn eine Saison gewechselt wird, diese nehmen, sonst die gespeicherte
$current_saison_id = $_GET['saison_id'] ?? $_POST['saison_id'] ?? $saved_saison_id;

$saisons = fetchSportwinnerAPI(['command' => 'GetSaisonArray']);
if (!$current_saison_id && !empty($saisons)) {
    $current_saison_id = $saisons[0][0]; // Automatisch die aktuellste Saison auswählen
}

$ligen = [];
if ($current_saison_id) {
    $ligen = fetchSportwinnerAPI([
        'command' => 'GetLigaArray',
        'id_saison' => $current_saison_id,
        'id_bezirk' => 0,
        'art' => 1, // 1 = NBKV-Ligen
        'favorit' => ''
    ]);
}

$pageTitle = "Tabelle konfigurieren";
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <h2>Aktuelle Tabelle Konfigurieren</h2>
    <div class="action-bar"><a href="dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a></div>

    <div class="content-tile" style="max-width: 800px;">
        <?php if ($success): ?>
            <div
                style="color: #155724; background: #d4edda; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <p style="margin-bottom: 20px;">Wähle hier die Liga aus, die auf der Webseite auf der Seite "Tabelle" angezeigt
            werden soll.</p>

        <form method="POST">
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Saison:</label>
                    <select name="saison_id" class="form-control"
                        onchange="window.location.href='?saison_id='+this.value" required>
                        <?php foreach ($saisons as $s): ?>
                            <option value="<?= htmlspecialchars($s[0]) ?>" <?= $current_saison_id == $s[0] ? 'selected' : '' ?>
                                >Saison
                                <?= htmlspecialchars($s[1]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 2;">
                    <label>Liga:</label>
                    <select name="liga_id" class="form-control" required>
                        <option value="">-- Bitte wählen --</option>
                        <?php foreach ($ligen as $liga): ?>
                            <option value="<?= htmlspecialchars($liga[0]) ?>" <?= ($_POST['liga_id'] ?? $saved_liga_id) == $liga[0] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($liga[2]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="save_tabelle" class="btn btn-primary"
                style="width: 100%; padding: 12px; font-size: 1.1rem;"><i class="fa-solid fa-save"></i> Tabelle
                speichern</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>