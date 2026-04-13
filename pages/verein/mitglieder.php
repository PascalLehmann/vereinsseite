<?php
session_start();
// Fehlerberichterstattung für die Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN (2 Ebenen nach oben ins Hauptverzeichnis)
require_once __DIR__ . '/../../db.php';

// 2. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>

        <main class="content">
            <h1>Unsere Mitglieder</h1>
            <p>Hier wird später die Mitgliederliste aus der Datenbank angezeigt.</p>
            <div
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="text-align: center; padding: 15px; border: 1px solid #eee;">
                    <div style="width: 80px; height: 80px; background: #ccc; border-radius: 50%; margin: 0 auto 10px;">
                    </div>
                    <strong>Max Mustermann</strong><br>Vorstand
                </div>
                <div style="text-align: center; padding: 15px; border: 1px solid #eee;">
                    <div style="width: 80px; height: 80px; background: #ccc; border-radius: 50%; margin: 0 auto 10px;">
                    </div>
                    <strong>Erika Muster</strong><br>Kassenwart
                </div>
            </div>
        </main>
        <?php
        // 3. FOOTER EINBINDEN
        require_once __DIR__ . '/../../templates/footer.php';
        ?>