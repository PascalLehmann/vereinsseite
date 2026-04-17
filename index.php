<?php
session_start();
// Fehlerberichterstattung für die Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Header einbinden (Hier öffnet sich der <html> und <body> Tag)
require_once __DIR__ . '/templates/header.php';

// Navigation einbinden (Hier liegt dein <nav> Tag)
require_once __DIR__ . '/templates/navigation.php';
?>

<main class="content">
    <div class="content-tile">
        <div class="home-header"
            style="display: flex; align-items: center; gap: 25px; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px;">
            <img src="/assets/img/verein/Vereinslogo.gif" alt="Logo SKV 9 Killer"
                style="max-width: 120px; height: auto;">
            <div>
                <h1 style="margin: 0; color: var(--sidebar-color); font-size: 1.8rem;">Willkommen beim</h1>
                <h2 style="margin: 5px 0 0 0; font-size: 2.2rem; color: #333;">SKV Nüünerkiller 16 Eisingen e.V.</h2>
            </div>
        </div>

        <p style="line-height: 1.8; font-size: 1.1rem; margin-bottom: 15px;">
            Wir sind ein <strong>2016 neu gegründeter Kegelverein</strong> aus Eisingen im Enzkreis. Aktuell sind wir
            noch auf der Suche
            nach neuen Spielern, die Lust auf Sportkegeln haben und ein gewisses Maß an Kampfgeist mitbringen.
        </p>
        <p style="line-height: 1.8; font-size: 1.1rem; margin-bottom: 25px;">
            Wir freuen uns über jeden Interessenten! Egal, in welchem Alter du bist oder ob Erfahrungen im Kegeln
            vorhanden sind – es ist jeder
            willkommen und herzlich zu einem Probetraining eingeladen. Wenn du jetzt Interesse hast mal vorbeizuschauen:
            Melde dich
            bei uns, komm zu einem Training vorbei und überzeug dich selbst, dass es viel Spaß macht zu kegeln.
        </p>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="/pages/verein/ueber-uns.php" class="btn btn-primary"><i class="fa-solid fa-circle-info"></i>
                Trainingszeiten & Kontakt</a>
            <a href="/pages/news/news.php" class="btn btn-secondary"><i class="fa-solid fa-newspaper"></i> Aktuelle
                News</a>
        </div>
    </div>

</main>

<?php
// Footer einbinden (Hier liegt dein <footer> und </body> schließt sich)
require_once __DIR__ . '/templates/footer.php';
?>