<?php
session_start();
// Fehlerberichterstattung für die Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Über uns";

// 1. DATENBANK EINBINDEN
require_once __DIR__ . '/../../db.php';

// 2. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <h1>Über den Verein</h1>
    <p>Erfahre mehr über unsere Geschichte, Trainingszeiten und wo du uns findest.</p>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">

        <!-- Karte: Adresse & Kontakt -->
        <div class="content-tile">
            <h3
                style="color: var(--sidebar-color); border-bottom: 2px solid var(--sidebar-color); padding-bottom: 5px; margin-bottom: 15px;">
                <i class="fa-solid fa-location-dot"></i> Adresse & Kontakt
            </h3>
            <p><strong>SKV Nüünerkiller 16 Eisingen e.V.</strong></p>
            <p>Talstraße 29-33<br>75239 Eisingen</p>
            <p style="margin-top: 15px;">
                <strong>Telefon:</strong> 0123 / 4567890<br>
                <strong>E-Mail:</strong> <a href="mailto:info@skv9killer.de"
                    style="color: var(--sidebar-color); text-decoration: none;">info@skv9killer.de</a>
            </p>
        </div>

        <!-- Karte: Trainingszeiten -->
        <div class="content-tile">
            <h3
                style="color: var(--sidebar-color); border-bottom: 2px solid var(--sidebar-color); padding-bottom: 5px; margin-bottom: 15px;">
                <i class="fa-solid fa-clock"></i> Trainingszeiten
            </h3>
            <p>Komm gerne vorbei und schau dir unser Training an. Neue Gesichter sind immer willkommen!</p>
            <ul style="list-style: none; padding: 0; margin-top: 15px;">
                <li style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Donnerstag:</strong> 15:00 - 19:00
                    Uhr
                </li>
            </ul>
        </div>

        <!-- Karte: Gründung & Historie -->
        <div class="content-tile" style="grid-column: 1 / -1;">
            <h3
                style="color: var(--sidebar-color); border-bottom: 2px solid var(--sidebar-color); padding-bottom: 5px; margin-bottom: 15px;">
                <i class="fa-solid fa-book-open"></i> Vereinsgeschichte
            </h3>
            <p>Der <strong>SKV Nüünerkiller 16 Eisingen e.V.</strong> wurde im Jahr <strong>2016</strong> von einer
                Gruppe
                leidenschaftlicher Kegler ins Leben gerufen.</p>
            <p style="margin-top: 10px; line-height: 1.6;">Seitdem haben wir uns nicht nur sportlich weiterentwickelt,
                sondern auch eine starke Gemeinschaft aufgebaut. Unser Fokus liegt auf sportlichem Ehrgeiz, Teamgeist
                und natürlich dem geselligen Beisammensein nach den Spieltagen. Wir blicken stolz auf
                10 Jahre Vereinsgeschichte zurück und freuen uns auf viele weitere erfolgreiche Jahre auf der
                Kegelbahn.</p>
        </div>
    </div>
</main>

<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>