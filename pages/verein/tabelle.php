<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../db.php';

// Hilfsfunktionen für Sportwinner
function fetchSportwinnerAPI($params)
{
    $apiUrl = 'https://blbk.sportwinner.de/php/blbk/service.php';
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                "Accept: application/json, text/javascript, */*; q=0.01\r\n" .
                "X-Requested-With: XMLHttpRequest\r\n" .
                "Referer: https://blbk.sportwinner.de/\r\n" .
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
            'content' => http_build_query($params)
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];
    $context = stream_context_create($options);
    $json = @file_get_contents($apiUrl, false, $context);
    return $json ? json_decode($json, true) : [];
}

function cleanSportwinnerHtml($html)
{
    if (empty($html))
        return '';
    $cleaned = preg_replace('/<div[^>]*class="[^"]*float-right[^"]*"[^>]*>.*?<\/div>/is', '', $html);
    $text = strip_tags($cleaned);
    $text = str_replace(['&nbsp;', '&#160;', "\xC2\xA0"], ' ', $text);
    return trim(preg_replace('/\s+/', ' ', $text));
}

// Lade die Einstellungen aus der Datenbank
$saison_id = '';
$liga_id = '';
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('sportwinner_saison_id', 'sportwinner_liga_id')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $saison_id = $settings['sportwinner_saison_id'] ?? '';
    $liga_id = $settings['sportwinner_liga_id'] ?? '';
} catch (PDOException $e) {
    // Falls die Tabelle noch nicht durch den Admin angelegt wurde, überspringen wir den Fehler
}

$tabelleData = [];
if ($saison_id && $liga_id) {
    $tabelleData = fetchSportwinnerAPI([
        'command' => 'GetTabelle',
        'id_saison' => $saison_id,
        'id_liga' => $liga_id,
        'sort' => 0,
        'nr_spieltag' => 100 // Nimmt immer den aktuellsten verfügbaren Spieltag
    ]);
}

$pageTitle = "Aktuelle Tabelle";
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <h1>Aktuelle Liga-Tabelle</h1>

    <div class="content-tile">
        <?php if (empty($saison_id) || empty($liga_id)): ?>
            <p style="text-align: center; color: #666; padding: 20px;">Die Tabelle wurde noch nicht im Admin-Bereich
                konfiguriert.</p>
        <?php elseif (empty($tabelleData) || !is_array($tabelleData)): ?>
            <p style="text-align: center; color: #e74c3c; padding: 20px;">Die Tabellendaten konnten nicht von Sportwinner
                geladen werden.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table" style="font-size: 0.95rem; width: 100%; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">Platz</th>
                            <th style="text-align: left;">Mannschaft</th>
                            <th style="text-align: center; width: 80px;">Spiele</th>
                            <th style="text-align: center; width: 100px;" title="Teampunkte">TP</th>
                            <th style="text-align: center; width: 100px;" title="Mannschaftspunkte">MP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $platzCounter = 1;
                        foreach ($tabelleData as $row):
                            $platz = $platzCounter++;
                            $mannschaft = cleanSportwinnerHtml($row[2] ?? '');

                            // "NBKV" aus dem Namen entfernen (inklusive möglicher Klammern)
                            $mannschaft = trim(str_ireplace(['(NBKV)', 'NBKV'], '', $mannschaft));

                            $spiele = cleanSportwinnerHtml($row[4] ?? '');

                            // TP (Teampunkte) mit Bindestrich statt Doppelpunkt
                            $tp = cleanSportwinnerHtml($row[7] ?? '0') . ' - ' . cleanSportwinnerHtml($row[10] ?? '0');

                            // MP (Mannschaftspunkte), wir entfernen ein mögliches ".0" für eine saubere Optik (z.B. 105 statt 105.0)
                            $mp_plus = str_replace('.0', '', cleanSportwinnerHtml($row[13] ?? '0'));
                            $mp_minus = str_replace('.0', '', cleanSportwinnerHtml($row[16] ?? '0'));
                            $mp = $mp_plus . ' - ' . $mp_minus;

                            // Highlight für den eigenen Verein
                            $isEigene = (stripos($mannschaft, 'Eisingen') !== false || stripos($mannschaft, 'Nüünerkiller') !== false);
                            ?>
                            <tr
                                style="<?= $isEigene ? 'background-color: rgba(230, 126, 34, 0.15); font-weight: bold; color: var(--sidebar-color);' : '' ?>">
                                <td style="text-align: center;"><?= htmlspecialchars($platz) ?>.</td>
                                <td><?= htmlspecialchars($mannschaft) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($spiele) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($tp) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($mp) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>