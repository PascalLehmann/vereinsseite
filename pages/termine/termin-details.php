<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ID-Validierung
$terminId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$pageTitle = "Termin Details";
include 'includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>

        <main class="content">
            <a href="termine.php" class="read-more" style="margin-bottom: 25px;">&laquo; Zurück zur Übersicht</a>

            <div class="news-card" style="margin-top: 20px;">
                <small>Kategorie: Training</small>
                <h1 style="color: var(--secondary-blue); margin: 10px 0;">Details zum Termin #<?php echo $terminId; ?>
                </h1>

                <div style="margin-top: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="padding: 15px; background: rgba(255,140,0,0.05); border-radius: 8px;">
                        <i class="fa-solid fa-calendar-days"
                            style="color: var(--primary-orange); margin-right: 10px;"></i>
                        <strong>Datum:</strong> 20.03.2024
                    </div>
                    <div style="padding: 15px; background: rgba(255,140,0,0.05); border-radius: 8px;">
                        <i class="fa-solid fa-clock" style="color: var(--primary-orange); margin-right: 10px;"></i>
                        <strong>Uhrzeit:</strong> 18:30 Uhr
                    </div>
                    <div style="padding: 15px; background: rgba(255,140,0,0.05); border-radius: 8px;">
                        <i class="fa-solid fa-location-dot"
                            style="color: var(--primary-orange); margin-right: 10px;"></i>
                        <strong>Ort:</strong> Sporthalle Musterstadt
                    </div>
                    <div style="padding: 15px; background: rgba(255,140,0,0.05); border-radius: 8px;">
                        <i class="fa-solid fa-tag" style="color: var(--primary-orange); margin-right: 10px;"></i>
                        <strong>Ereignis:</strong> Training Jugend
                    </div>
                </div>

                <div
                    style="margin-top: 30px; line-height: 1.7; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin-bottom: 15px;">Zusätzliche Informationen</h3>
                    <p>Hier werden später alle zusätzlichen Informationen stehen, die wir in der Datenbank für diesen
                        speziellen Termin hinterlegen.</p>
                    <p>Das Training richtet sich an alle Altersklassen von 10 bis 16 Jahren. Bitte pünktlich erscheinen
                        und Sportkleidung sowie Hallenschuhe mitbringen. Neue Interessenten sind herzlich zu einem
                        Probetraining eingeladen!</p>
                </div>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>

</html>