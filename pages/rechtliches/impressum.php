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

<main class="content">
    <h1>Impressum</h1>

    <h3>Angaben gemäß § 5 TMG</h3>
    <p>
        <strong>[Dein Vereinsname e.V.]</strong><br>
        [Straße Hausnummer]<br>
        [PLZ Ort]
    </p>

    <h3>Vertreten durch den Vorstand</h3>
    <p>
        [Vorname Nachname (1. Vorsitzender)]<br>
        [Vorname Nachname (2. Vorsitzender)]
    </p>

    <h3>Kontakt</h3>
    <p>
        Telefon: [Deine Telefonnummer]<br>
        E-Mail: [Deine E-Mail-Adresse]
    </p>

    <h3>Registereintrag</h3>
    <p>
        Eintragung im Vereinsregister.<br>
        Registergericht: [Amtsgericht Ort]<br>
        Registernummer: VR [Deine Nummer]
    </p>

    <h3>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h3>
    <p>
        [Vorname Nachname]<br>
        [Straße Hausnummer]<br>
        [PLZ Ort]
    </p>
</main>
<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>