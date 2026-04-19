<?php
session_start();

// --- GEMEINSAME SPORTWINNER API FUNKTION ---
if (!function_exists('fetchSportwinnerAPI')) {
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
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ];
        $context = stream_context_create($options);
        $json = @file_get_contents($apiUrl, false, $context);
        return $json ? json_decode($json, true) : [];
    }
}

// --- PROXY FÜR AJAX LIGEN-ABRUF (MUSS GANZ OBEN STEHEN) ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_ligen') {
    require_once __DIR__ . '/../../../db.php';
    $saison_id = $_GET['saison_id'] ?? '';
    header('Content-Type: application/json');
    echo json_encode(fetchSportwinnerAPI(['command' => 'GetLigaArray', 'id_saison' => $saison_id, 'id_bezirk' => 0, 'art' => 1, 'favorit' => '']));
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
$canNewsEdit = !empty($perms['news_edit']);
$canNewsDelete = !empty($perms['news_delete']);
$canNewsDeleteHard = !empty($perms['news_delete_hard']);
if (!$canNewsEdit) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: uebersicht.php");
    exit;
}

// News-Details laden
$stmtNews = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmtNews->execute([$id]);
$news = $stmtNews->fetch(PDO::FETCH_ASSOC);
if (!$news) {
    header("Location: uebersicht.php");
    exit;
}

// Bilder für die News laden (auch die gelöschten, wenn man die Berechtigung hat)
$stmtBilder = $pdo->prepare("SELECT id, bild_pfad, is_deleted FROM news_bilder WHERE news_id = ? ORDER BY id ASC");
$stmtBilder->execute([$id]);
$bilder = $stmtBilder->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "News bearbeiten";
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>News bearbeiten</h2>

    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="content-tile"
        style="max-width: 800px;">
        <input type="hidden" name="id" value="<?= $news['id'] ?>">

        <div class="form-group">
            <label for="titel">Titel:</label>
            <input type="text" id="titel" name="titel" class="form-control"
                value="<?= htmlspecialchars($news['titel']) ?>" required>
        </div>

        <div class="form-group">
            <label for="inhalt">Text:</label>
            <textarea id="inhalt" name="inhalt" rows="8"
                class="form-control"><?= htmlspecialchars($news['inhalt']) ?></textarea>
        </div>

        <!-- NEU: SPORTWINNER SPIELTAG KOPPLUNG -->
        <div class="form-group">
            <label
                style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: bold; padding: 10px; background: #eef2f5; border-radius: 5px;">
                <input type="checkbox" id="is_spielbericht" name="is_spielbericht" value="1"
                    onchange="toggleSpielberichtFields()" style="width: 20px; height: 20px;"
                    <?= !empty($news['is_spielbericht']) ? 'checked' : '' ?>>
                News ist ein Spielbericht (Ergebnisse von Sportwinner laden)
            </label>
        </div>

        <div id="spielbericht_fields"
            style="display: <?= !empty($news['is_spielbericht']) ? 'block' : 'none' ?>; background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px dashed #ccc;">
            <div class="form-group">
                <label for="sw_saison_id">Saison:</label>
                <select id="sw_saison_id" name="sw_saison_id" class="form-control" onchange="loadLigen()">
                    <?php
                    $saisons = fetchSportwinnerAPI(['command' => 'GetSaisonArray']);
                    if (is_array($saisons)) {
                        foreach ($saisons as $s) {
                            $selected = ($news['sw_saison_id'] == $s[0]) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($s[0]) . '" ' . $selected . '>Saison ' . htmlspecialchars($s[1]) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sw_liga_id">Liga:</label>
                <select id="sw_liga_id" name="sw_liga_id" class="form-control">
                    <option value="">-- Zuerst Saison wählen --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sw_spieltag">Spieltag (Nummer):</label>
                <input type="number" id="sw_spieltag" name="sw_spieltag" class="form-control" min="1" max="50"
                    placeholder="z.B. 4" value="<?= htmlspecialchars($news['sw_spieltag'] ?? '') ?>">
            </div>
        </div>

        <div class="file-upload-box">
            <label for="bilder">Weitere Bilder hinzufügen:</label>
            <input type="file" id="bilder" name="bilder[]" multiple accept=".jpg, .jpeg, .png, .webp"
                class="form-control" style="border: none; padding: 0;">
            <small style="color: #666; display: block; margin-top: 5px;">Erlaubt: JPG, PNG, WEBP. Max. 5MB pro
                Bild.</small>
        </div>

        <!-- NEU: Bilder-Verwaltung -->
        <?php if (count($bilder) > 0): ?>
            <hr style="margin: 25px 0;">
            <h3>Bilder verwalten</h3>
            <div class="news-gallery-admin">
                <?php foreach ($bilder as $bild): ?>
                    <?php if ($bild['is_deleted'] && !$canNewsDeleteHard)
                        continue; ?>
                    <div class="admin-image-wrapper <?= $bild['is_deleted'] ? 'deleted' : '' ?>">
                        <img src="<?= htmlspecialchars($bild['bild_pfad']) ?>" alt="News Bild">
                        <div class="admin-image-actions">
                            <?php if ($bild['is_deleted']): ?>
                                <?php if ($canNewsDeleteHard): // Nur wer endgültig löschen darf, darf auch wiederherstellen ?>
                                    <a href="bild_aktion.php?action=restore&bild_id=<?= $bild['id'] ?>&news_id=<?= $id ?>"
                                        class="btn-restore" title="Wiederherstellen" style="margin-right: 10px;">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <a href="bild_aktion.php?action=hard_delete&bild_id=<?= $bild['id'] ?>&news_id=<?= $id ?>"
                                        class="btn-delete" title="Endgültig löschen"
                                        onclick="return confirm('Bild wirklich ENDGÜLTIG vom Server löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($canNewsDelete): ?>
                                    <a href="bild_aktion.php?action=delete&bild_id=<?= $bild['id'] ?>&news_id=<?= $id ?>"
                                        class="btn-delete" title="Löschen"
                                        onclick="return confirm('Bild archivieren? Es kann später wiederhergestellt werden.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Ende Bilder-Verwaltung -->

        <button type="submit" class="btn btn-primary"
            style="width: 100%; margin-top: 25px; padding: 12px; font-size: 1.1rem;">Änderungen speichern</button>
    </form>

    <script>
        CKEDITOR.replace('inhalt', {
            height: 300,
            language: 'de',
            versionCheck: false
        });

        // JavaScript für die Sportwinner-Felder
        function toggleSpielberichtFields() {
            var cb = document.getElementById('is_spielbericht');
            var fields = document.getElementById('spielbericht_fields');
            fields.style.display = cb.checked ? 'block' : 'none';
        }

        function loadLigen(preselectLigaId = null) {
            var saisonId = document.getElementById('sw_saison_id').value;
            var ligaSelect = document.getElementById('sw_liga_id');
            ligaSelect.innerHTML = '<option value="">Lade Ligen...</option>';

            if (!saisonId) {
                ligaSelect.innerHTML = '<option value="">-- Zuerst Saison wählen --</option>';
                return;
            }

            fetch('bearbeiten.php?ajax=get_ligen&saison_id=' + saisonId)
                .then(response => response.json())
                .then(data => {
                    ligaSelect.innerHTML = '<option value="">-- Liga wählen --</option>';
                    if (Array.isArray(data)) {
                        data.forEach(function (liga) {
                            var opt = document.createElement('option');
                            opt.value = liga[0];
                            opt.textContent = liga[2];
                            if (preselectLigaId && preselectLigaId == liga[0]) {
                                opt.selected = true;
                            }
                            ligaSelect.appendChild(opt);
                        });
                    }
                })
                .catch(err => {
                    ligaSelect.innerHTML = '<option value="">Fehler beim Laden</option>';
                });
        }

        // Beim Laden die gespeicherte Liga wiederherstellen
        document.addEventListener('DOMContentLoaded', function () {
            var savedLigaId = "<?= htmlspecialchars($news['sw_liga_id'] ?? '') ?>";
            if (document.getElementById('sw_saison_id').value !== "") {
                loadLigen(savedLigaId);
            }
        });
    </script>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>