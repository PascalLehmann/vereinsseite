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
            <h1>Vereins-Verwaltung</h1>
            <p>Wähle einen Bereich zum Bearbeiten:</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-top: 30px;">
                
                <a href="news-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-bottom: 4px solid var(--primary-orange);">
                        <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: var(--primary-orange); margin-bottom: 15px;"></i>
                        <h3>News</h3>
                    </div>
                </a>

                <a href="termine-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-bottom: 4px solid #3498db;">
                        <i class="fa-solid fa-calendar-check" style="font-size: 3rem; color: #3498db; margin-bottom: 15px;"></i>
                        <h3>Termine</h3>
                    </div>
                </a>

                <a href="mitglieder-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-bottom: 4px solid #2ecc71;">
                        <i class="fa-solid fa-user-group" style="font-size: 3rem; color: #2ecc71; margin-bottom: 15px;"></i>
                        <h3>Mitglieder</h3>
                    </div>
                </a>

                <a href="gegner-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-bottom: 4px solid #e74c3c;">
                        <i class="fa-solid fa-shield" style="font-size: 3rem; color: #e74c3c; margin-bottom: 15px;"></i>
                        <h3>Gegner</h3>
                    </div>
                </a>

            </div>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>