<?php
// Debug-Modus: Zeigt alle Fehler direkt im Browser an
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Startseite - Mein Verein";

// Header lädt automatisch Navigation
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>Willkommen beim SKV9Killer</h1>
            <p>Das Grundgerüst steht. Dies ist der Inhaltsbereich auf weißem Hintergrund mit schwarzer Schrift.</p>
        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>