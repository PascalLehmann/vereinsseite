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

<main>
    <h2>Willkommen beim SKV9Killer</h2>
    <p>Das Grundgerüst steht. Dies ist der Inhaltsbereich auf weißem Hintergrund mit schwarzer Schrift.</p>

</main>

<?php
// Footer einbinden (Hier liegt dein <footer> und </body> schließt sich)
require_once __DIR__ . '/templates/footer.php';
?>