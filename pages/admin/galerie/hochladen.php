<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$perms = $_SESSION['permissions'] ?? [];
$canGalerieUpload = !empty($perms['galerie_upload']);
if (!$canGalerieUpload) {
    die("Zugriff verweigert. Du benötigst das Recht, Bilder hochzuladen.");
}

require_once __DIR__ . '/../../../db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['bilder']) && $_FILES['bilder']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = __DIR__ . '/../../../uploads/galerie/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0755, true);
        $kategorie_id = !empty($_POST['kategorie_id']) ? (int) $_POST['kategorie_id'] : null;

        $erlaubte_formate = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024;
        $stmtBild = $pdo->prepare("INSERT INTO galerie_bilder (bild_pfad, kategorie_id) VALUES (?, ?)");

        $count = count($_FILES['bilder']['name']);
        for ($i = 0; $i < $count; $i++) {
            $tmp_name = $_FILES['bilder']['tmp_name'][$i];
            $error_code = $_FILES['bilder']['error'][$i];
            $size = $_FILES['bilder']['size'][$i];

            if ($error_code === UPLOAD_ERR_OK) {
                if ($size <= $max_size) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);

                    if (in_array($mime_type, $erlaubte_formate)) {
                        $dateiendung = pathinfo($_FILES['bilder']['name'][$i], PATHINFO_EXTENSION);
                        $neuer_dateiname = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $dateiendung;
                        $ziel_pfad_absolut = $upload_dir . $neuer_dateiname;
                        $ziel_pfad_db = '/uploads/galerie/' . $neuer_dateiname;

                        if (move_uploaded_file($tmp_name, $ziel_pfad_absolut)) {
                            $stmtBild->execute([$ziel_pfad_db, $kategorie_id]);
                        }
                    }
                }
            }
        }
        header("Location: uebersicht.php");
        exit;
    } else {
        $error = "Bitte wähle mindestens ein Bild aus.";
    }
}

$kategorien = $pdo->query("SELECT id, name FROM galerie_kategorien WHERE is_deleted = 0 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>
<main>
    <div class="action-bar"><a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a></div>
    <h2>Bilder zur Galerie hinzufügen</h2>

    <div class="content-tile" style="max-width: 600px;">
        <?php if ($error): ?>
            <p class="alert-error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>
        <form action="hochladen.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Kategorie auswählen:</label>
                <select name="kategorie_id" class="form-control" required>
                    <option value="">-- Bitte wählen --</option>
                    <?php foreach ($kategorien as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
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
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>