<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
$pageTitle = "Impressum"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
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
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>