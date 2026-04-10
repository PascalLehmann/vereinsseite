<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$typ = isset($_GET['typ']) ? $_GET['typ'] : 'mitglied';
$pageTitle = "Profil Details"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <a href="<?= ($typ == 'vorstand') ? 'vorstand.php' : 'spieler.php'; ?>" class="read-more" style="margin-bottom: 25px;">
                &laquo; Zurück zur Übersicht
            </a>
            
            <article class="news-card" style="margin-top: 20px; display: flex; gap: 30px; align-items: center;">
                <div class="profile-preview-circle" style="margin: 0; flex-shrink: 0; width: 180px; height: 180px;">
                    <img src="https://via.placeholder.com/200" alt="Profilbild">
                </div>
                
                <div>
                    <h1>Name des <?= ucfirst($typ); ?>s</h1>
                    <p style="font-size: 1.2rem; color: var(--primary-orange); font-weight: bold;">
                        <?= ($typ == 'vorstand') ? 'Vorstandsamt' : 'Spielerposition'; ?>
                    </p>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    <p>Hier stehen die Detailinformationen, Kontakte oder Statistiken.</p>
                </div>
            </article>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>