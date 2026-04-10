<?php 
// 1. Sicherheit zuerst: Session prüfen
include_once 'auth.php';
checkLogin();

// 2. Datenbank und Header laden (Pfade gehen eine Ebene höher)
include_once '../db.php';
$pageTitle = "Admin Dashboard";
include_once '../includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container"> 
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Willkommen im Admin-Bereich</h1>
            <p>Wähle eine Funktion aus der Übersicht oder dem Menü links.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <a href="news-admin.php" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; transition: 0.3s; cursor: pointer; border-top: 5px solid var(--primary-orange);">
                        <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: var(--primary-orange); margin: 15px 0;"></i>
                        <h3>News verwalten</h3>
                        <p>Beiträge schreiben, ändern oder löschen.</p>
                    </div>
                </a>

                <div class="news-card" style="text-align: center; opacity: 0.5; border-top: 5px solid #ccc;">
                    <i class="fa-solid fa-users" style="font-size: 3rem; color: #ccc; margin: 15px 0;"></i>
                    <h3>Mitglieder</h3>
                    <p>In Kürze verfügbar.</p>
                </div>

            </div>
        </main>
        
    </div> <?php include_once '../includes/footer.php'; ?>
</div> 