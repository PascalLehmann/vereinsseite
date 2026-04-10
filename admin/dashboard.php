<?php
include_once 'auth.php';
checkLogin();
$pageTitle = "Admin Dashboard";
include_once '../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Willkommen im Admin-Bereich</h1>
            <p>Was möchtest du heute verwalten?</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <a href="news-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center;">
                        <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: var(--primary-orange);"></i>
                        <h3>News</h3>
                    </div>
                </a>

                <a href="termine-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center;">
                        <i class="fa-solid fa-calendar-days" style="font-size: 3rem; color: #3498db;"></i>
                        <h3>Termine</h3>
                    </div>
                </a>

                <a href="mitglieder-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center;">
                        <i class="fa-solid fa-users" style="font-size: 3rem; color: #2ecc71;"></i>
                        <h3>Mitglieder</h3>
                    </div>
                </a>

                <a href="gegner-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center;">
                        <i class="fa-solid fa-shield-halved" style="font-size: 3rem; color: #e74c3c;"></i>
                        <h3>Gegner</h3>
                    </div>
                </a>

            </div>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>