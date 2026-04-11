<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$pageTitle = "Vorstand";
$activePage = 'vorstand.php'; // Für die Fokus-Logik im Menü
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php'; ?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>

        <main class="content">
            <h1>Unser Vorstand</h1>
            <p>Die gewählten Vertreter unseres Vereins.</p>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 30px;">

                <article class="news-card" style="text-align: center;">
                    <div class="profile-preview-circle">
                        <img src="https://via.placeholder.com/150" alt="1. Vorsitzender">
                    </div>
                    <h2>Max Mustermann</h2>
                    <p><strong>1. Vorsitzender</strong></p>
                    <p style="font-size: 0.9rem; color: #666; margin: 10px 0;">Leitung des Vereins und Repräsentation
                        nach außen.</p>
                    <a href="mitglied-details.php?id=1&typ=vorstand" class="read-more">Kontakt & Info</a>
                </article>

                <article class="news-card" style="text-align: center;">
                    <div class="profile-preview-circle">
                        <img src="https://via.placeholder.com/150" alt="2. Vorsitzender">
                    </div>
                    <h2>Erika Musterfrau</h2>
                    <p><strong>2. Vorsitzende</strong></p>
                    <p style="font-size: 0.9rem; color: #666; margin: 10px 0;">Stellvertretende Leitung und
                        Projektkoordination.</p>
                    <a href="mitglied-details.php?id=2&typ=vorstand" class="read-more">Kontakt & Info</a>
                </article>

                <article class="news-card" style="text-align: center;">
                    <div class="profile-preview-circle">
                        <img src="https://via.placeholder.com/150" alt="Schatzmeister">
                    </div>
                    <h2>Lukas Lohngeld</h2>
                    <p><strong>Schatzmeister</strong></p>
                    <p style="font-size: 0.9rem; color: #666; margin: 10px 0;">Verantwortlich für Finanzen und
                        Mitgliederverwaltung.</p>
                    <a href="mitglied-details.php?id=3&typ=vorstand" class="read-more">Kontakt & Info</a>
                </article>

            </div>
        </main>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>

</div>
</body>

</html>