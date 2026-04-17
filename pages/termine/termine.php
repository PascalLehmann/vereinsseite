<?php
session_start();
require_once __DIR__ . '/../../db.php';
$pageTitle = "Termine & Spieltage";

// Header & Navigation (angepasst an unsere Architektur)
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';

// SQL-Abfrage: Wir verknüpfen den Termin mit dem Gegner
$sql = "SELECT t.*, g.name AS gegner_name, g.strasse, g.plz, g.ort AS gegner_ort, g.bahnen 
        FROM termine t 
        LEFT JOIN gegner g ON t.gegner_id = g.id 
        WHERE t.is_deleted = 0
        ORDER BY t.termin_datum ASC";
$stmt = $pdo->query($sql);
$termine = $stmt->fetchAll();

// Hilfsfunktion um Spielernamen/Bild zu holen
function getSpielerInfo($id, $pdo)
{
    if (!$id)
        return null;
    $stmt = $pdo->prepare("SELECT vorname, nachname, profilbild FROM mitglieder WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>

<main>
    <h2>Termine & Spieltage</h2>

    <div class="termine-liste">
        <?php foreach ($termine as $t): ?>
            <article class="content-tile"
                style="border-left: 6px solid <?= ($t['typ'] === 'spiel') ? '#e67e22' : '#3498db' ?>;">

                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">

                    <div class="termin-date-box">
                        <span class="termin-date-day">
                            <?= date("d.m.", strtotime($t['termin_datum'])) ?>
                        </span>
                        <span class="termin-date-year">
                            <?= date("Y", strtotime($t['termin_datum'])) ?>
                        </span>
                        <hr style="margin: 8px 0; border: 0; border-top: 1px solid #ddd;">
                        <span class="termin-date-time">
                            <?= $t['uhrzeit'] ? date("H:i", strtotime($t['uhrzeit'])) . ' Uhr' : 'TBA' ?>
                        </span>
                    </div>

                    <div class="termin-details">
                        <?php if ($t['typ'] === 'spiel'): ?>
                            <h3 style="margin-bottom: 5px; font-size: 1.4rem;">
                                SKV 9 Killers vs.
                                <?= htmlspecialchars($t['gegner_name'] ?? '') ?>
                            </h3>
                            <p
                                style="color: <?= ($t['heimspiel'] ? '#e67e22' : '#3498db') ?>; font-weight: bold; text-transform: uppercase; font-size: 0.85rem; margin-bottom: 10px;">
                                <?= ($t['heimspiel'] ? '<i class="fa-solid fa-house"></i> Heimspiel' : '<i class="fa-solid fa-bus"></i> Auswärtsspiel') ?>
                            </p>

                            <div class="termin-extra-info"
                                style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">

                                <div style="font-size: 0.95rem; color: #555;">
                                    <strong><i class="fa-solid fa-location-dot"></i> Ort:</strong>
                                    <?php if ($t['heimspiel']): ?>
                                        Unsere Kegelbahn (Musterstr. 1, 12345 Stadt)
                                    <?php else: ?>
                                        <?= htmlspecialchars($t['strasse'] . ", " . $t['plz'] . " " . $t['gegner_ort']) ?>
                                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($t['strasse'] . " " . $t['plz'] . " " . $t['gegner_ort']) ?>"
                                            target="_blank"
                                            style="color: #3498db; margin-left: 10px; text-decoration: none; font-weight: bold;">
                                            <i class="fa-solid fa-map-location-dot"></i> Maps
                                        </a>
                                        <?php if (!empty($t['bahnen'])): ?>
                                            <span style="margin-left: 10px; color: #e67e22;"><i class="fa-solid fa-bowling-ball"></i>
                                                Bahnen: <?= htmlspecialchars($t['bahnen']) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <p style="font-size: 0.95rem; margin-top: 8px; color: #555;">
                                    <strong><i class="fa-solid fa-play"></i> Spielbeginn:</strong>
                                    <?= $t['uhrzeit'] ? date("H:i", strtotime($t['uhrzeit'])) . ' Uhr' : 'Noch nicht bekannt' ?>
                                </p>

                                <?php if (!$t['heimspiel'] && ($t['treffpunkt_zeit'] || $t['treffpunkt_ort'])): ?>
                                    <p style="font-size: 0.95rem; margin-top: 8px; color: #555;">
                                        <strong><i class="fa-solid fa-handshake"></i> Treffpunkt:</strong>
                                        <?= $t['treffpunkt_zeit'] ? date("H:i", strtotime($t['treffpunkt_zeit'])) . ' Uhr' : '' ?>
                                        <?= ($t['treffpunkt_zeit'] && $t['treffpunkt_ort']) ? ' - ' : '' ?>
                                        <?= $t['treffpunkt_ort'] ? htmlspecialchars($t['treffpunkt_ort']) : '' ?>
                                    </p>
                                <?php endif; ?>

                                <button onclick="toggleAufstellung(<?= $t['id'] ?>)" class="btn btn-primary btn-sm"
                                    style="margin-top: 15px;">
                                    <i class="fa-solid fa-users-viewfinder"></i> Kader anzeigen
                                </button>
                            </div>

                        <?php else: ?>
                            <h3 style="margin-bottom: 5px; font-size: 1.4rem;">
                                <?= htmlspecialchars($t['titel'] ?: ($t['veranstaltungsart'] ?? '')) ?>
                            </h3>
                            <?php if (!empty($t['veranstaltungsart'])): ?>
                                <p
                                    style="color: #3498db; font-weight: bold; text-transform: uppercase; font-size: 0.85rem; margin-bottom: 10px;">
                                    <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($t['veranstaltungsart']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="termin-extra-info"
                                style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                                <?php if (!empty($t['beschreibung'])): ?>
                                    <p style="margin-bottom: 10px; color: #555;">
                                        <?= nl2br(htmlspecialchars($t['beschreibung'])) ?>
                                    </p>
                                <?php endif; ?>
                                <p style="font-size: 0.95rem; color: #555;">
                                    <strong><i class="fa-solid fa-location-dot"></i> Ort/Treffpunkt:</strong>
                                    <?= htmlspecialchars($t['ort'] ?? 'Kein Ort angegeben') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTerminDetails(this)"
                            style="margin-top: 15px;">
                            <i class="fa-solid fa-chevron-down"></i> Mehr erfahren
                        </button>
                    </div>
                </div>

                <?php if ($t['typ'] === 'spiel'): ?>
                    <div id="aufstellung-<?= $t['id'] ?>"
                        style="display:none; margin-top: 25px; padding-top: 20px; border-top: 1px dashed #ccc;">
                        <h4 style="text-align: center; margin-bottom: 20px; color: #333;">
                            Kader für diesen Spieltag
                        </h4>

                        <div class="kader-grid">
                            <?php
                            $kader = [
                                'Stamm' => [$t['s1'], $t['s2'], $t['s3'], $t['s4'], $t['s5'], $t['s6']],
                                'Ersatz' => [$t['a1'], $t['a2'], $t['a3']]
                            ];

                            foreach ($kader as $gruppe => $ids):
                                foreach ($ids as $s_id):
                                    $s = getSpielerInfo($s_id, $pdo);
                                    if (!$s)
                                        continue; // Wenn ID leer oder 0 ist, überspringen
                                    ?>
                                    <div class="spieler-card">
                                        <div class="spieler-avatar">
                                            <img src="<?= !empty($s['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($s['profilbild']) : '/assets/img/mitglieder/default-user.png' ?>"
                                                alt="Spieler">
                                            <?php if ($s_id == $t['spielfuehrer_id']): ?>
                                                <div title="Spielführer" class="spielfuehrer-badge">
                                                    <i class="fa-solid fa-star"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-weight: bold; font-size: 0.9rem; color: #333; line-height: 1.2;">
                                            <?= htmlspecialchars($s['vorname']) ?><br>
                                            <?= htmlspecialchars($s['nachname']) ?>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </article>
        <?php endforeach; ?>
    </div>
</main>

<script>
    function toggleAufstellung(id) {
        // Ein kleiner jQuery-Trick für weiches Ein-/Ausblenden (da wir jQuery ja im Header haben!)
        $('#aufstellung-' + id).slideToggle(300);
    }

    function toggleTerminDetails(btn) {
        const $extra = $(btn).siblings('.termin-extra-info');
        $extra.slideToggle(300, function () {
            if ($extra.is(':visible')) {
                $(btn).html('<i class="fa-solid fa-chevron-up"></i> Weniger anzeigen');
            } else {
                $(btn).html('<i class="fa-solid fa-chevron-down"></i> Mehr erfahren');
                // Schließe den Kader direkt mit, wenn die Details eingeklappt werden
                $(btn).closest('.content-tile').find('div[id^="aufstellung-"]').slideUp(300);
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>