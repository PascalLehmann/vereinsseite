<?php
include_once 'db.php';
$pageTitle = "Termine & Spieltage";
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';

// SQL-Abfrage: Wir verknüpfen den Termin mit dem Gegner
$sql = "SELECT t.*, g.name AS gegner_name, g.strasse, g.plz, g.ort AS gegner_ort 
        FROM termine t 
        LEFT JOIN gegner g ON t.gegner_id = g.id 
        ORDER BY t.termin_datum ASC";
$stmt = $pdo->query($sql);
$termine = $stmt->fetchAll();

// Hilfsfunktion um Spielernamen/Bild zu holen (da wir 9 Spieler-IDs haben)
function getSpielerInfo($id, $pdo)
{
    if (!$id)
        return null;
    $stmt = $pdo->prepare("SELECT vorname, nachname, profilbild FROM mitglieder WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once 'includes/nav.php'; ?>

        <main class="content">
            <h1>Termine & Spieltage</h1>

            <div class="termine-liste">
                <?php foreach ($termine as $t): ?>
                    <div class="news-card"
                        style="border-left: 8px solid <?= ($t['typ'] === 'spiel') ? '#e67e22' : '#3498db' ?>;">

                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">

                            <div
                                style="background: #f4f7f6; padding: 15px; border-radius: 15px; text-align: center; min-width: 100px;">
                                <span
                                    style="display:block; font-size: 1.2rem; font-weight: bold; color: var(--secondary-blue);">
                                    <?= date("d.m.", strtotime($t['termin_datum'])) ?>
                                </span>
                                <span
                                    style="font-size: 0.9rem; color: #666;"><?= date("Y", strtotime($t['termin_datum'])) ?></span>
                                <hr style="margin: 5px 0; border: 0; border-top: 1px solid #ddd;">
                                <span style="font-weight: bold;"><?= date("H:i", strtotime($t['uhrzeit'])) ?> Uhr</span>
                            </div>

                            <div style="flex: 1; padding: 0 20px;">
                                <?php if ($t['typ'] === 'spiel'): ?>
                                    <h2 style="margin-bottom: 5px;">
                                        SKV 9 Killers vs. <?= htmlspecialchars($t['gegner_name']) ?>
                                    </h2>
                                    <p style="color: #e67e22; font-weight: bold; text-transform: uppercase; font-size: 0.8rem;">
                                        <?= ($t['heimspiel'] ? '🏠 Heimspiel' : '🚌 Auswärtsspiel') ?>
                                    </p>

                                    <div style="margin-top: 10px; font-size: 0.9rem;">
                                        <strong>Ort:</strong>
                                        <?php if ($t['heimspiel']): ?>
                                            Unsere Kegelbahn (Musterstr. 1, 12345 Stadt)
                                        <?php else: ?>
                                            <?= htmlspecialchars($t['strasse'] . ", " . $t['plz'] . " " . $t['gegner_ort']) ?>
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($t['strasse'] . " " . $t['gegner_ort']) ?>"
                                                target="_blank" style="color: #3498db; margin-left: 10px;">
                                                <i class="fa-solid fa-map-location-dot"></i> Google Maps
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!$t['heimspiel'] && $t['treffpunkt_zeit']): ?>
                                        <p style="font-size: 0.9rem; margin-top: 5px;">
                                            <strong>Treffpunkt:</strong> <?= date("H:i", strtotime($t['treffpunkt_zeit'])) ?> Uhr
                                        </p>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <h2 style="margin-bottom: 5px;">
                                        <?= htmlspecialchars($t['titel'] ?: $t['veranstaltungsart']) ?>
                                    </h2>
                                    <p><?= nl2br(htmlspecialchars($t['beschreibung'])) ?></p>
                                    <p><strong>Treffpunkt/Ort:</strong> <?= htmlspecialchars($t['ort']) ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if ($t['typ'] === 'spiel'): ?>
                                <button onclick="toggleAufstellung(<?= $t['id'] ?>)" class="read-more"
                                    style="white-space: nowrap;">
                                    <i class="fa-solid fa-users-viewfinder"></i> Aufstellung
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if ($t['typ'] === 'spiel'): ?>
                            <div id="aufstellung-<?= $t['id'] ?>"
                                style="display:none; margin-top: 30px; padding-top: 20px; border-top: 2px dashed #eee;">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--secondary-blue);">
                                    Kader für diesen Spieltag
                                </h3>

                                <div
                                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                                    <?php
                                    $kader = [
                                        'Stamm' => [$t['s1'], $t['s2'], $t['s3'], $t['s4'], $t['s5'], $t['s6']],
                                        'Ersatz' => [$t['a1'], $t['a2'], $t['a3']]
                                    ];

                                    foreach ($kader as $gruppe => $ids):
                                        foreach ($ids as $s_id):
                                            $s = getSpielerInfo($s_id, $pdo);
                                            if (!$s)
                                                continue;
                                            ?>
                                            <div
                                                style="text-align: center; background: white; padding: 10px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #eee;">
                                                <div class="profile-preview-circle"
                                                    style="width: 70px; height: 70px; margin: 0 auto 10px; position: relative;">
                                                    <img src="<?= getProfilbild($s['profilbild']) ?>" alt="Spieler">
                                                    <?php if ($s_id == $t['spielfuehrer_id']): ?>
                                                        <div title="Spielführer"
                                                            style="position: absolute; bottom: -5px; right: -5px; background: #f1c40f; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white;">
                                                            <i class="fa-solid fa-star" style="color: white; font-size: 0.7rem;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-weight: bold; font-size: 0.85rem;">
                                                    <?= htmlspecialchars($s['vorname']) ?>
                                                </div>
                                                <div style="font-size: 0.7rem; color: #999;"><?= $gruppe ?></div>
                                            </div>
                                        <?php endforeach; endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>

</div>

<script>
    function toggleAufstellung(id) {
        const el = document.getElementById('aufstellung-' + id);
        if (el.style.display === 'none') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }
</script>