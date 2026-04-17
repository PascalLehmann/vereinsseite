<?php
session_start();
// Fehlerberichterstattung für die Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN
require_once __DIR__ . '/../../db.php';

// 2. LAYOUT EINBINDEN
$pageTitle = "Vereinsbestenliste";
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';

// 3. DATEN FÜR DIE LISTEN LADEN
// Bestenliste 120 Wurf
$stmt120 = $pdo->query("
    SELECT vorname, nachname, best_120_wert, best_120_datum, best_120_ort 
    FROM mitglieder 
    WHERE best_120_wert IS NOT NULL AND best_120_wert > 0
    ORDER BY best_120_wert DESC
");
$bestenliste_120 = $stmt120->fetchAll(PDO::FETCH_ASSOC);

// Bestenliste 100 Wurf
$stmt100 = $pdo->query("
    SELECT vorname, nachname, best_100_wert, best_100_datum, best_100_ort 
    FROM mitglieder 
    WHERE best_100_wert IS NOT NULL AND best_100_wert > 0
    ORDER BY best_100_wert DESC
");
$bestenliste_100 = $stmt100->fetchAll(PDO::FETCH_ASSOC);

// Bestenliste 200 Wurf
$stmt200 = $pdo->query("
    SELECT vorname, nachname, best_200_wert, best_200_datum, best_200_ort 
    FROM mitglieder 
    WHERE best_200_wert IS NOT NULL AND best_200_wert > 0
    ORDER BY best_200_wert DESC
");
$bestenliste_200 = $stmt200->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="content">
    <h1>Vereinsbestenliste</h1>
    <p>Die ewigen Bestenlisten unseres Vereins in den verschiedenen Disziplinen.</p>

    <div class="bestenlisten-grid">
        <?php
        function render_bestenliste($titel, $liste, $wert_key, $datum_key, $ort_key)
        {
            ob_start(); ?>
            <div class="content-tile">
                <h3 class="bestenliste-header">
                    <?= $titel ?>
                </h3>
                <ol class="bestenliste">
                    <?php if (count($liste) > 0): ?>
                        <?php foreach ($liste as $index => $e): ?>
                            <li class="<?= $index >= 3 ? 'hidden-item' : '' ?>" style="<?= $index >= 3 ? 'display: none;' : '' ?>">
                                <div class="bestenliste-platz">
                                    <?= $index + 1 ?>.
                                </div>
                                <div class="bestenliste-details">
                                    <div class="bestenliste-name-wert">
                                        <strong>
                                            <?= htmlspecialchars($e['vorname'] . ' ' . $e['nachname']) ?>
                                        </strong>
                                        <span class="bestenliste-wert">
                                            <?= htmlspecialchars($e[$wert_key]) ?> Holz
                                        </span>
                                    </div>
                                    <div class="bestenliste-meta">
                                        <?= !empty($e[$ort_key]) ? htmlspecialchars($e[$ort_key]) : '' ?>
                                        <?= !empty($e[$datum_key]) ? ' am ' . date("d.m.Y", strtotime($e[$datum_key])) : '' ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="bestenliste-leer">Noch keine Einträge vorhanden.</p>
                    <?php endif; ?>
                </ol>
                <?php if (count($liste) > 3): ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleList(this)">Mehr anzeigen</button>
                    </div>
                <?php endif; ?>
            </div>
            <?php return ob_get_clean();
        }

        echo render_bestenliste('120 Wurf', $bestenliste_120, 'best_120_wert', 'best_120_datum', 'best_120_ort');
        echo render_bestenliste('100 Wurf', $bestenliste_100, 'best_100_wert', 'best_100_datum', 'best_100_ort');
        echo render_bestenliste('200 Wurf', $bestenliste_200, 'best_200_wert', 'best_200_datum', 'best_200_ort');
        ?>
    </div>
</main>

<script>
function toggleList(btn) {
    const ol = btn.parentElement.previousElementSibling;
    const hiddenItems = ol.querySelectorAll('.hidden-item');
    
    let isShowing = btn.getAttribute('data-showing') === 'true';
    
    if (isShowing) {
        hiddenItems.forEach(item => item.style.display = 'none');
        btn.innerText = 'Mehr anzeigen';
        btn.setAttribute('data-showing', 'false');
    } else {
        hiddenItems.forEach(item => item.style.display = 'flex');
        btn.innerText = 'Weniger anzeigen';
        btn.setAttribute('data-showing', 'true');
    }
}
</script>

<?php
require_once __DIR__ . '/../../templates/footer.php';
?>