<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ID der gewählten Galerie-Kategorie abgreifen (analog zu argv in C)
$galerieId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Später holen wir den echten Namen aus der DB (z.B. "Sommerfest 2025")
$kategorieName = "Sommerfest 2025 (Demo #" . $galerieId . ")";

$pageTitle = "Bilder: " . $kategorieName;

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <a href="galerie.php" class="read-more" style="margin-bottom: 25px;">
        <i class="fa-solid fa-arrow-left"></i> Zurück zur Galerie
    </a>

    <h1>Sommerfest 2025</h1>
    <p>Hier siehst du alle Fotos dieser Kategorie.</p>

    <div class="photo-grid">
        <div class="photo-card">
            <div class="photo-box">
                <a href="pfad/zu/bild.jpg" target="_blank">
                    <img src="pfad/zu/bild.jpg" alt="Vorschau">
                </a>
            </div>
        </div>
    </div>
</main>

<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>