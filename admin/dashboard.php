<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Admin Dashboard";
include_once '../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Willkommen im Admin-Bereich</h1>
            <p>Was möchtest du heute erledigen?</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <a href="news-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; transition: 0.3s; cursor: pointer; border-top: 5px solid var(--primary-orange);">
                        <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: var(--primary-orange); margin-bottom: 15px;"></i>
                        <h3>News verwalten</h3>
                        <p>Beiträge schreiben, bearbeiten oder löschen.</p>
                    </div>
                </a>

                <div class="news-card" style="text-align: center; opacity: 0.5; border-top: 5px solid #ccc;">
                    <i class="fa-solid fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <h3>Mitglieder</h3>
                    <p>(In Arbeit - kommt bald)</p>
                </div>

                <div class="news-card" style="text-align: center; opacity: 0.5; border-top: 5px solid #ccc;">
                    <i class="fa-solid fa-calendar-days" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <h3>Termine</h3>
                    <p>(In Arbeit - kommt bald)</p>
                </div>

            </div>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>