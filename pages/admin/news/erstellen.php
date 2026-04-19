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
$canNewsCreate = !empty($perms['news_create']);
if (!$canNewsCreate) {
    die("Zugriff verweigert. Du benötigst das Recht, News zu erstellen.");
}

// 2. DATENBANK EINBINDEN
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../image_helper.php';

$error = '';
$success = '';

// 3. FORMULAR WURDE ABGESCHICKT (POST-Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = trim($_POST['titel'] ?? '');
    $inhalt = trim($_POST['inhalt'] ?? '');
    $autor_id = $_SESSION['user_id']; // Wer ist gerade eingeloggt?

    $is_spielbericht = isset($_POST['is_spielbericht']) ? 1 : 0;
    $sw_saison_id = $_POST['sw_saison_id'] ?? null;
    $sw_liga_id = $_POST['sw_liga_id'] ?? null;
    $sw_spieltag = !empty($_POST['sw_spieltag']) ? (int) $_POST['sw_spieltag'] : null;

    if (empty($titel) || empty($inhalt)) {
        $error = "Titel und Inhalt dürfen nicht leer sein.";
    } else {
        try {
            // ATOMARE TRANSAKTION STARTEN (Alles oder nichts!)
            $pdo->beginTransaction();

            // A) Die News in die Haupttabelle einfügen
            $sqlNews = "INSERT INTO news (titel, inhalt, autor_id, is_spielbericht, sw_saison_id, sw_liga_id, sw_spieltag) 
                        VALUES (:titel, :inhalt, :autor_id, :is_spielbericht, :sw_saison_id, :sw_liga_id, :sw_spieltag)";
            $stmtNews = $pdo->prepare($sqlNews);
            $stmtNews->execute([
                ':titel' => $titel,
                ':inhalt' => $inhalt,
                ':autor_id' => $autor_id,
                ':is_spielbericht' => $is_spielbericht,
                ':sw_saison_id' => $sw_saison_id,
                ':sw_liga_id' => $sw_liga_id,
                ':sw_spieltag' => $sw_spieltag
            ]);

            // Den "Pointer" (die ID) des neu erstellten Eintrags holen
            $news_id = $pdo->lastInsertId();

            // B) Die Bilder verarbeiten, falls welche ausgewählt wurden
            if (isset($_FILES['bilder']) && $_FILES['bilder']['error'][0] !== UPLOAD_ERR_NO_FILE) {

                // Wir definieren den absoluten Pfad zum Upload-Ordner (vom aktuellen Skript aus)
                $upload_dir = __DIR__ . '/../../../uploads/news/';

                // Falls der Ordner nicht existiert -> erstellen (mit sicheren 755 Rechten)
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $erlaubte_formate = ['image/jpeg', 'image/png', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5 MB Limit pro Bild

                $sqlBild = "INSERT INTO news_bilder (news_id, bild_pfad) VALUES (:news_id, :bild_pfad)";
                $stmtBild = $pdo->prepare($sqlBild);

                // Schleife durch alle hochgeladenen Dateien
                $count = count($_FILES['bilder']['name']);
                for ($i = 0; $i < $count; $i++) {
                    $tmp_name = $_FILES['bilder']['tmp_name'][$i];
                    $error_code = $_FILES['bilder']['error'][$i];
                    $size = $_FILES['bilder']['size'][$i];

                    if ($error_code === UPLOAD_ERR_OK) {
                        // 1. Check: Dateigröße
                        if ($size > $max_size) {
                            throw new Exception("Eine Datei ist größer als 5MB.");
                        }

                        // 2. Check: Echter Datei-Typ (nicht nur die Endung raten!)
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime_type = $finfo->file($tmp_name);

                        if (!in_array($mime_type, $erlaubte_formate)) {
                            throw new Exception("Falsches Dateiformat. Nur JPG, PNG und WEBP sind erlaubt.");
                        }

                        // 3. Dateiendung prüfen & sicheren Namen generieren
                        $dateiendung = strtolower(pathinfo($_FILES['bilder']['name'][$i], PATHINFO_EXTENSION));
                        $erlaubte_endungen = ['jpg', 'jpeg', 'png', 'webp'];
                        if (!in_array($dateiendung, $erlaubte_endungen)) {
                            throw new Exception("Die Dateiendung ist nicht erlaubt.");
                        }

                        $neuer_dateiname = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $dateiendung;
                        $ziel_pfad_absolut = $upload_dir . $neuer_dateiname;

                        // Pfad für die Datenbank (relativ zum Web-Root)
                        $ziel_pfad_db = '/uploads/news/' . $neuer_dateiname;

                        // 4. Datei aus dem RAM/Temp in den finalen Ordner verschieben
                        if (resizeAndCompressImage($tmp_name, $ziel_pfad_absolut, 1920, 80) || move_uploaded_file($tmp_name, $ziel_pfad_absolut)) {
                            // Pfad in die Datenbank schreiben
                            $stmtBild->execute([
                                ':news_id' => $news_id,
                                ':bild_pfad' => $ziel_pfad_db
                            ]);
                        } else {
                            throw new Exception("Fehler beim Speichern der Datei auf dem Server.");
                        }
                    }
                }
            }

            // WENN ALLES GUT GING: Transaktion bestätigen!
            $pdo->commit();
            header("Location: uebersicht.php?success=1");
            exit;

        } catch (Exception $e) {
            // FEHLER! Wir brechen alles ab und löschen die angelegte News wieder!
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// 4. LAYOUT EINBINDEN
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Neue News erstellen</h2>

    <div style="margin-bottom: 20px;">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <?php if ($error): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="erstellen.php" method="POST" enctype="multipart/form-data" style="max-width: 600px;">

        <div class="form-group">
            <label for="titel">Titel:</label>
            <input type="text" id="titel" name="titel" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="inhalt">Text:</label>
            <textarea id="inhalt" name="inhalt" rows="8" class="form-control"></textarea>
        </div>

        <!-- NEU: SPORTWINNER SPIELTAG KOPPLUNG -->
        <div class="form-group">
            <label
                style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: bold; padding: 10px; background: #eef2f5; border-radius: 5px;">
                <input type="checkbox" id="is_spielbericht" name="is_spielbericht" value="1"
                    onchange="toggleSpielberichtFields()" style="width: 20px; height: 20px;">
                News ist ein Spielbericht (Ergebnisse von Sportwinner laden)
            </label>
        </div>

        <div id="spielbericht_fields"
            style="display: none; background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px dashed #ccc;">
            <div class="form-group">
                <label for="sw_saison_id">Saison:</label>
                <select id="sw_saison_id" name="sw_saison_id" class="form-control" onchange="loadLigen()">
                    <?php
                    $saisons = fetchSportwinnerAPI(['command' => 'GetSaisonArray']);
                    if (is_array($saisons)) {
                        $first = true;
                        foreach ($saisons as $s) {
                            echo '<option value="' . htmlspecialchars($s[0]) . '" ' . ($first ? 'selected' : '') . '>Saison ' . htmlspecialchars($s[1]) . '</option>';
                            $first = false;
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
                    placeholder="z.B. 4">
            </div>
        </div>

        <div class="file-upload-box">
            <label for="bilder">Bilder hinzufügen (Optional):</label>
            <input type="file" id="bilder" name="bilder[]" multiple accept=".jpg, .jpeg, .png, .webp"
                class="form-control" style="border: none; padding: 0;">
            <small style="color: #666; display: block; margin-top: 5px;">Erlaubt: JPG, PNG, WEBP. Max. 5MB pro
                Bild.</small>
        </div>

        <button type="submit" class="btn btn-primary">News Speichern</button>
    </form>

    <script>
        CKEDITOR.replace('inhalt', {
            height: 300,
            language: 'de',
            versionCheck: false
        });

        // NEU: JavaScript für die Sportwinner-Felder
        function toggleSpielberichtFields() {
            var cb = document.getElementById('is_spielbericht');
            var fields = document.getElementById('spielbericht_fields');
            fields.style.display = cb.checked ? 'block' : 'none';
        }

        function loadLigen() {
            var saisonId = document.getElementById('sw_saison_id').value;
            var ligaSelect = document.getElementById('sw_liga_id');
            ligaSelect.innerHTML = '<option value="">Lade Ligen...</option>';

            if (!saisonId) {
                ligaSelect.innerHTML = '<option value="">-- Zuerst Saison wählen --</option>';
                return;
            }

            fetch('erstellen.php?ajax=get_ligen&saison_id=' + saisonId)
                .then(response => response.json())
                .then(data => {
                    ligaSelect.innerHTML = '<option value="">-- Liga wählen --</option>';
                    if (Array.isArray(data)) {
                        data.forEach(function (liga) {
                            var opt = document.createElement('option');
                            opt.value = liga[0];
                            opt.textContent = liga[2]; // Index 2 ist der Liga-Name
                            ligaSelect.appendChild(opt);
                        });
                    }
                })
                .catch(err => {
                    ligaSelect.innerHTML = '<option value="">Fehler beim Laden</option>';
                });
        }

        // Beim Laden der Seite direkt die Ligen der vorausgewählten Saison abrufen
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('sw_saison_id').value !== "") {
                loadLigen();
            }
        });
    </script>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>