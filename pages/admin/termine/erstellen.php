<?php
include_once 'auth.php';
checkLogin();
$pageTitle = "Termin-Typ wählen";
include_once '../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Was für einen Termin möchtest du anlegen?</h1>
            <p>Wähle eine Vorlage für ein einheitliches Design:</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <a href="termin-formular.php?typ=spiel" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-top: 5px solid #e74c3c;">
                        <i class="fa-solid fa-trophy" style="font-size: 2.5rem; color: #e74c3c; margin-bottom: 10px;"></i>
                        <h3>Spieltag</h3>
                        <p>Mit Gegner, Treffpunkt & Trikots</p>
                    </div>
                </a>

                <a href="termin-formular.php?typ=training" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-top: 5px solid #3498db;">
                        <i class="fa-solid fa-dumbbell" style="font-size: 2.5rem; color: #3498db; margin-bottom: 10px;"></i>
                        <h3>Training</h3>
                        <p>Reguläres Vereinstraining</p>
                    </div>
                </a>

                <a href="termin-formular.php?typ=standard" style="text-decoration: none; color: inherit;">
                    <div class="news-card" style="text-align: center; border-top: 5px solid var(--primary-orange);">
                        <i class="fa-solid fa-calendar-check" style="font-size: 2.5rem; color: var(--primary-orange); margin-bottom: 10px;"></i>
                        <h3>Allgemein</h3>
                        <p>Sitzungen, Feste, etc.</p>
                    </div>
                </a>

            </div>
        </main>
    </div>
    <?php include_once '../includes/footer.php'; ?>
</div>