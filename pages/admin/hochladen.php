<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
if (empty($perms['galerie_upload'])) {
    die("Zugriff verweigert. Du benötigst das Recht, Bilder hochzuladen.");
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/image_helper.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategorie_id = filter_input(INPUT_POST, 'kategorie_id', FILTER_VALIDATE_INT);

    if (!$kategorie_id) {
        $error = "Bitte wähle eine Kategorie für die Bilder aus.";
    } elseif (isset($_FILES['bilder']) && $_FILES['bilder']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = __DIR__ . '/../../uploads/galerie/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0755, true);

        $erlaubte_formate = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024;
        $stmtBild = $pdo->prepare("INSERT INTO galerie_bilder (kategorie_id, bild_pfad) VALUES (?, ?)");

        $count = count($_FILES['bilder']['name']);
        for ($i = 0; $i < $count; $i++) {
            $tmp_name = $_FILES['bilder']['tmp_name'][$i];
            $error_code = $_FILES['bilder']['error'][$i];
            $size = $_FILES['bilder']['size'][$i];

            if ($error_code === UPLOAD_ERR_OK) {
                if ($size <= $max_size) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime_type = $finfo->file($tmp_name);

                    if (in_array($mime_type, $erlaubte_formate)) {
                        $dateiendung = strtolower(pathinfo($_FILES['bilder']['name'][$i], PATHINFO_EXTENSION));
                        $erlaubte_endungen = ['jpg', 'jpeg', 'png', 'webp'];
                        if (!in_array($dateiendung, $erlaubte_endungen)) {
                            die("Sicherheitswarnung: Die Dateiendung ist nicht erlaubt.");
                        }

                        $neuer_dateiname = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $dateiendung;
                        $ziel_pfad_absolut = $upload_dir . $neuer_dateiname;
                        $ziel_pfad_db = '/uploads/galerie/' . $neuer_dateiname;

                        if (resizeAndCompressImage($tmp_name, $ziel_pfad_absolut, 1920, 80) || move_uploaded_file($tmp_name, $ziel_pfad_absolut)) {
                            $stmtBild->execute([$kategorie_id, $ziel_pfad_db]);
                        }
                    }
                }
            }
        }
        header("Location: galerie/uebersicht.php");
        exit;
    } else {
        $error = "Bitte wähle mindestens ein Bild aus.";
    }
}

require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';

// Lade alle verfügbaren Galerie-Kategorien für das Dropdown-Menü
$stmtKategorien = $pdo->query("SELECT id, name FROM galerie_kategorien WHERE is_deleted = 0 ORDER BY name ASC");
$kategorien = $stmtKategorien->fetchAll(PDO::FETCH_ASSOC);
?>
<main>
    <div class="action-bar"><a href="galerie/uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>
    <h2>Bilder zur Galerie hinzufügen</h2>

    <div class="content-tile" style="max-width: 800px;"> <!-- HIER DIE GRÖSSE DIESES SPEZIELLEN PANELS ÄNDERN -->
        <?php if ($error): ?>
            <p class="alert-error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>
        <form action="hochladen.php" method="POST" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Kategorie auswählen:</label>
                <select name="kategorie_id" class="form-control" required>
                    <option value="">-- Bitte wählen --</option>
                    <?php foreach ($kategorien as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="file-upload-box">
                <label>Bilder auswählen (Mehrfachauswahl möglich):</label>
                <input type="file" name="bilder[]" multiple accept=".jpg, .jpeg, .png, .webp" class="form-control"
                    style="border: none; padding: 0;" required>
                <small style="color: #666; display: block; margin-top: 5px;">Erlaubt: JPG, PNG, WEBP. Max. 5MB pro
                    Bild.</small>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 10px;">Jetzt
                hochladen</button>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../../templates/footer.php'; ?>