<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert.");
}

// 2. DATENBANK EINBINDEN
require_once __DIR__ . '/../../../db.php';

$error = '';
$success = '';

// 3. FORMULAR WURDE ABGESCHICKT (POST-Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = trim($_POST['titel'] ?? '');
    $inhalt = trim($_POST['inhalt'] ?? '');
    $autor_id = $_SESSION['user_id']; // Wer ist gerade eingeloggt?

    if (empty($titel) || empty($inhalt)) {
        $error = "Titel und Inhalt dürfen nicht leer sein.";
    } else {
        try {
            // ATOMARE TRANSAKTION STARTEN (Alles oder nichts!)
            $pdo->beginTransaction();

            // A) Die News in die Haupttabelle einfügen
            $sqlNews = "INSERT INTO news (titel, inhalt, autor_id) VALUES (:titel, :inhalt, :autor_id)";
            $stmtNews = $pdo->prepare($sqlNews);
            $stmtNews->execute([
                ':titel' => $titel,
                ':inhalt' => $inhalt,
                ':autor_id' => $autor_id
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
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $tmp_name);
                        finfo_close($finfo);

                        if (!in_array($mime_type, $erlaubte_formate)) {
                            throw new Exception("Falsches Dateiformat. Nur JPG, PNG und WEBP sind erlaubt.");
                        }

                        // 3. Sicheren Dateinamen generieren (z.B. 64b3a12..._bild.jpg)
                        $dateiendung = pathinfo($_FILES['bilder']['name'][$i], PATHINFO_EXTENSION);
                        $neuer_dateiname = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $dateiendung;
                        $ziel_pfad_absolut = $upload_dir . $neuer_dateiname;

                        // Pfad für die Datenbank (relativ zum Web-Root)
                        $ziel_pfad_db = '/uploads/news/' . $neuer_dateiname;

                        // 4. Datei aus dem RAM/Temp in den finalen Ordner verschieben
                        if (move_uploaded_file($tmp_name, $ziel_pfad_absolut)) {
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
            header("Location: übersicht.php?success=1");
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
        <a href="übersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
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
            language: 'de'
        });
    </script>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>