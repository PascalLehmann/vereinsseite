<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN
require_once __DIR__ . '/../../db.php';

// 2. ID AUS DER URL HOLEN UND PRÜFEN
$news_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($news_id <= 0) {
    die("Ungültige News-ID.");
}

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="news.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <?php
    try {
        // A) Die spezifische News abfragen
        $sql = "SELECT n.*, u.username as autor_name 
                FROM news n 
                LEFT JOIN users u ON n.autor_id = u.id 
                WHERE n.id = :id AND n.is_deleted = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $news_id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($news) {
            echo "<article class='content-tile'>";

            // Titel, Datum und Autor
            echo "<h2 style='margin-bottom: 5px;'>" . htmlspecialchars($news['titel']) . "</h2>";
            $autor = !empty($news['autor_name']) ? htmlspecialchars($news['autor_name']) : 'Unbekannt';
            echo "<small style='color: #6b7280; display: block; margin-bottom: 20px;'><i class='fas fa-calendar-alt'></i> " . date('d.m.Y H:i', strtotime($news['erstellt_am'])) . " Uhr &nbsp;|&nbsp; <i class='fas fa-user'></i> " . $autor . "</small>";

            // B) Der Hauptinhalt vom CKEditor
            echo "<div class='news-content'>";
            // html_entity_decode wandelt eventuell kodierte HTML-Elemente wieder in echte Tags um
            echo html_entity_decode($news['inhalt'], ENT_QUOTES, 'UTF-8');
            echo "</div>";

            // =================================================================
            // NEU: SPORTWINNER ERGEBNISSE AUSGEBEN
            // =================================================================
            if (!empty($news['is_spielbericht']) && !empty($news['sw_saison_id']) && !empty($news['sw_liga_id']) && !empty($news['sw_spieltag'])) {

                // Hilfsfunktionen (falls noch nicht definiert)
                if (!function_exists('fetchSportwinnerAPI')) {
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
                            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                        ];
                        $context = stream_context_create($options);
                        $json = @file_get_contents($apiUrl, false, $context);
                        return $json ? json_decode($json, true) : [];
                    }
                }
                if (!function_exists('cleanSportwinnerHtml')) {
                    function cleanSportwinnerHtml($html)
                    {
                        if ($html === null || $html === '')
                            return '';
                        // WICHTIG: Wenn Sportwinner eine reine Zahl schickt, in Text umwandeln!
                        if (is_numeric($html))
                            return (string) $html;
                        if (!is_string($html))
                            return '';
                        $cleaned = preg_replace('/<div[^>]*class="[^"]*float-right[^"]*"[^>]*>.*?<\/div>/is', '', $html);
                        $text = strip_tags($cleaned);
                        return trim(preg_replace('/\s+/', ' ', str_replace(['&nbsp;', '&#160;', "\xC2\xA0"], ' ', $text)));
                    }
                }

                if (!function_exists('parsePlayerNameAndStats')) {
                    function parsePlayerNameAndStats($html)
                    {
                        if ($html === null || $html === '') {
                            return ['name' => '', 'hasStats' => false, 'stats' => ['V' => '-', 'A' => '-', 'F' => '-']];
                        }

                        // Name extrahieren (alles vor einem eventuellen Zeilenumbruch)
                        $parts = preg_split('/<br[^>]*>/i', (string) $html);
                        $name = cleanSportwinnerHtml($parts[0]);

                        $stats = ['V' => '-', 'A' => '-', 'F' => '-'];
                        $hasStats = false;
                        $fullText = strip_tags(str_replace(['<br>', '<br/>'], ' ', (string) $html));

                        if (preg_match('/V:\s*(\d+)/i', $fullText, $m)) {
                            $stats['V'] = $m[1];
                            $hasStats = true;
                        }
                        if (preg_match('/A:\s*(\d+)/i', $fullText, $m)) {
                            $stats['A'] = $m[1];
                            $hasStats = true;
                        }
                        if (preg_match('/F:\s*(\d+)/i', $fullText, $m)) {
                            $stats['F'] = $m[1];
                            $hasStats = true;
                        }

                        return ['name' => $name, 'hasStats' => $hasStats, 'stats' => $stats];
                    }
                }

                // Spielplan abrufen
                $spielplanResponse = fetchSportwinnerAPI([
                    'command' => 'GetSpielplan',
                    'id_saison' => $news['sw_saison_id'],
                    'id_liga' => $news['sw_liga_id']
                ]);

                // Falls die API im Datatables-Format ("data": [...]) antwortet
                $spielplanData = $spielplanResponse['data'] ?? $spielplanResponse;

                // Wir suchen im gesamten Spielplan nach der Begegnung am korrekten Spieltag
                $unserSpiel = null;
                $gesuchter_spieltag = (int) $news['sw_spieltag'];
                $match_counter = 0; // Zählt unsere Spiele chronologisch durch
    
                if (is_array($spielplanData)) {
                    foreach ($spielplanData as $match) {
                        if (!is_array($match))
                            continue;
                        $rowString = implode(' ', $match);

                        if (
                            stripos($rowString, 'Eisingen') !== false || stripos($rowString, 'Nüünerkiller') !== false
                        ) {
                            $match_counter++;

                            // Manchmal steht in Spalte 0 die Spieltag-Nummer
                            $row_spieltag_raw = cleanSportwinnerHtml($match[0] ?? '');
                            preg_match('/\d+/', $row_spieltag_raw, $m);
                            $row_spieltag = isset($m[0]) ? (int) $m[0] : 0;

                            // Treffer: Entweder Spalte 0 stimmt, oder es ist das x-te Spiel chronologisch
                            if ($row_spieltag === $gesuchter_spieltag || $match_counter === $gesuchter_spieltag) {
                                $unserSpiel = $match;
                                break;
                            }
                        }
                    }
                }

                echo "<div class='sportwinner-results' style='margin-top: 35px; background: #ffffff; padding: 20px; border-radius: 10px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>";
                echo "<h3 style='margin-bottom: 15px; color: var(--sidebar-color); text-align: center;'><i class='fas fa-trophy'></i> Spielbericht - " . htmlspecialchars($news['sw_spieltag']) . ". Spieltag</h3>";

                if ($unserSpiel) {
                    $id_spiel = 0;
                    $datum = '';
                    $zeit = '';
                    $heim = '';
                    $gast = '';
                    $holz_heim = '-';
                    $holz_gast = '-';
                    $punkte_heim = '-';
                    $punkte_gast = '-';

                    // Format-Check: Manchmal ändert Sportwinner die Spalten (wie in deinem Debug)
                    $raw_id = trim(strip_tags($unserSpiel[1] ?? ''));
                    if (isset($unserSpiel[4]) && isset($unserSpiel[5]) && preg_match('/^\d{5,}$/', $raw_id)) {
                        // NEUES FORMAT (z.B. ID in Spalte 1, Datum/Zeit in Spalte 3)
                        $id_spiel = (int) $raw_id;
                        $dz_parts = explode('-', cleanSportwinnerHtml($unserSpiel[3] ?? ''));
                        $datum = trim($dz_parts[0] ?? '');
                        $zeit = trim($dz_parts[1] ?? '');
                        $heim = cleanSportwinnerHtml($unserSpiel[4]);
                        $gast = cleanSportwinnerHtml($unserSpiel[5]);
                        $punkte_heim = cleanSportwinnerHtml($unserSpiel[7] ?? '-');
                        $punkte_gast = cleanSportwinnerHtml($unserSpiel[8] ?? '-');
                    } else {
                        // ALTES FORMAT
                        if (!empty($unserSpiel['DT_RowId'])) {
                            $id_spiel = (int) preg_replace('/\D/', '', $unserSpiel['DT_RowId']);
                        }
                        if (!$id_spiel) {
                            $rowString = implode(' ', $unserSpiel);
                            if (preg_match('/bericht.*?(\d{3,})/i', $rowString, $matches)) {
                                $id_spiel = (int) $matches[1];
                            } elseif (preg_match('/id_spiel=(\d+)/i', $rowString, $matches)) {
                                $id_spiel = (int) $matches[1];
                            } else {
                                $cleanRow = strip_tags($rowString);
                                if (preg_match('/(?<!\d)(\d{5,})(?!\d)/', $cleanRow, $matches)) {
                                    $id_spiel = (int) $matches[1];
                                }
                            }
                        }

                        $datum = cleanSportwinnerHtml($unserSpiel[1] ?? '');
                        $zeit = cleanSportwinnerHtml($unserSpiel[2] ?? '');
                        $heim = cleanSportwinnerHtml($unserSpiel[3] ?? 'Heimmannschaft');
                        $gast = cleanSportwinnerHtml($unserSpiel[4] ?? 'Gastmannschaft');
                        $holz_heim = cleanSportwinnerHtml($unserSpiel[5] ?? '-');
                        $holz_gast = cleanSportwinnerHtml($unserSpiel[6] ?? '-');
                        $punkte_heim = cleanSportwinnerHtml($unserSpiel[7] ?? '-');
                        $punkte_gast = cleanSportwinnerHtml($unserSpiel[8] ?? '-');
                    }

                    // =================================================================
                    // FEHLENDER BLOCK: Datenabfrage & Vorab-Berechnung
                    // =================================================================
    
                    // =================================================================
                    // API-ABFRAGEN FÜR DEBUGGING ERWEITERT
                    // Wir rufen jetzt alle 3 bekannten Befehle ab, um zu prüfen, 
                    // in welchem Array sich die "Zeile 10" mit dem Kommentar versteckt.
                    // =================================================================
    
                    $api_GetSpielbericht = fetchSportwinnerAPI(['command' => 'GetSpielbericht', 'id_spiel' => $id_spiel]);
                    $api_GetSpielerInfo = fetchSportwinnerAPI(['command' => 'GetSpielerInfo', 'id_saison' => $news['sw_saison_id'], 'id_spiel' => $id_spiel, 'wertung' => '1']);
                    $api_GetBericht = fetchSportwinnerAPI(['command' => 'GetBericht', 'id_spiel' => $id_spiel]);

                    // =================================================================
                    // NEU: Den internen 'id_spieltag' (z.B. 96882) dynamisch ermitteln
                    // =================================================================
                    $api_Spieltage = fetchSportwinnerAPI([
                        'command' => 'GetSpieltagArray',
                        'id_saison' => $news['sw_saison_id'],
                        'id_liga' => $news['sw_liga_id'],
                        'id_bezirk' => '0'
                    ]);
                    $stData = $api_Spieltage['data'] ?? (is_array($api_Spieltage) ? $api_Spieltage : []);
                    $internal_spieltag_id = '';
                    foreach ($stData as $st) {
                        $stLabel = isset($st[1]) ? trim((string) $st[1]) : '';
                        $stVal = isset($st[0]) ? trim((string) $st[0]) : '';

                        // Robustere Erkennung: Sucht nach "17", "17.", "17 " oder als reine Zahl
                        if (
                            $stLabel === (string) $news['sw_spieltag'] ||
                            strpos($stLabel, $news['sw_spieltag'] . '.') === 0 ||
                            strpos($stLabel, $news['sw_spieltag'] . ' ') === 0 ||
                            (int) $stLabel === (int) $news['sw_spieltag']
                        ) {
                            $internal_spieltag_id = $stVal;
                            break;
                        }
                    }
                    if (empty($internal_spieltag_id)) {
                        $internal_spieltag_id = (string) $news['sw_spieltag']; // Fallback
                    }

                    // Die exakte, erfolgreiche Spezialanfrage mit dynamischen Parametern absetzen
                    $api_GetSpiel = fetchSportwinnerAPI([
                        'command' => 'GetSpiel',
                        'id_saison' => $news['sw_saison_id'],
                        'id_klub' => '0',
                        'id_bezirk' => '0',
                        'id_liga' => $news['sw_liga_id'],
                        'id_spieltag' => $internal_spieltag_id,
                        'favorit' => '',
                        'art_bezirk' => '1',
                        'art_liga' => '0',
                        'art_spieltag' => '2'
                    ]);

                    // Extrahiere die eigentlichen Daten (falls sie in 'data' gewrappt sind)
                    $data_GetSpielbericht = isset($api_GetSpielbericht['data']) ? $api_GetSpielbericht['data'] : (is_array($api_GetSpielbericht) ? $api_GetSpielbericht : []);
                    $data_GetSpielerInfo = isset($api_GetSpielerInfo['data']) ? $api_GetSpielerInfo['data'] : (is_array($api_GetSpielerInfo) ? $api_GetSpielerInfo : []);
                    $data_GetBericht = isset($api_GetBericht['data']) ? $api_GetBericht['data'] : (is_array($api_GetBericht) ? $api_GetBericht : []);
                    $data_GetSpiel = isset($api_GetSpiel['data']) ? $api_GetSpiel['data'] : (is_array($api_GetSpiel) ? $api_GetSpiel : []);

                    // Wir wählen automatisch das Array aus, das die MEISTEN Zeilen hat 
                    // (z.B. 11 Zeilen statt 10, weil dort die Kommentarzeile enthalten ist)
                    $spielberichtData = [];
                    foreach ([$data_GetSpielbericht, $data_GetSpielerInfo, $data_GetBericht] as $arr) {
                        if (is_array($arr) && count($arr) > count($spielberichtData)) {
                            $spielberichtData = $arr;
                        }
                    }

                    // Fallback, falls alle fehlschlagen, probieren wir es nochmal nur mit id statt id_spiel
                    if (empty($spielberichtData)) {
                        $fallback = fetchSportwinnerAPI(['command' => 'GetSpielbericht', 'id' => $id_spiel]);
                        $spielberichtData = isset($fallback['data']) ? $fallback['data'] : (is_array($fallback) ? $fallback : []);
                    }

                    // Vorab-Berechnung der Summen und Kommentare für den Titel-Bereich
                    $is120Wurf = false;
                    $summenZeile = null;
                    $spielKommentar = '';

                    // =================================================================
                    // NEU: Kommentar exakt aus der GetSpiel-Abfrage (Spalte 10) extrahieren!
                    // =================================================================
                    // Wir durchsuchen das detaillierte GetSpiel Array und als Fallback den Spielplan
                    $searchArrays = [$data_GetSpiel, $spielplanData];

                    foreach ($searchArrays as $arr) {
                        if (!empty($arr) && is_array($arr)) {
                            foreach ($arr as $matchRow) {
                                // id_spiel steht manchmal in Spalte 0, manchmal in 1
                                $rowId0 = isset($matchRow[0]) ? (int) cleanSportwinnerHtml($matchRow[0]) : 0;
                                $rowId1 = isset($matchRow[1]) ? (int) cleanSportwinnerHtml($matchRow[1]) : 0;

                                if ($rowId0 === $id_spiel || $rowId1 === $id_spiel) {
                                    // Spalten 8 bis 12 großzügig auf den Einwechseltext absuchen
                                    for ($c = 8; $c <= 12; $c++) {
                                        if (!empty($matchRow[$c])) {
                                            $kommentarRaw = cleanSportwinnerHtml($matchRow[$c]);
                                            if ($kommentarRaw !== '' && stripos($kommentarRaw, 'undefined') === false) {
                                                // Verifizieren, ob es sich um den typischen Einwechseltext handelt
                                                if (preg_match('/(für.*?nach|Wurf|Auswechslung|;)/i', $kommentarRaw) || strlen($kommentarRaw) > 25) {
                                                    $kommentarTeile = explode(';', $kommentarRaw);
                                                    $kommentarTeile = array_map('trim', $kommentarTeile);
                                                    $formatted = implode("\n", array_filter($kommentarTeile));
                                                    if (strpos($spielKommentar, $formatted) === false) {
                                                        $spielKommentar .= ($spielKommentar ? "\n" : "") . $formatted;
                                                    }
                                                    break 3; // Spiel und Text gefunden, Schleifen abbrechen
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (is_array($spielberichtData) && count($spielberichtData) > 0) {
                        foreach ($spielberichtData as $r) {
                            if (is_array($r) && count($r) >= 15) {
                                $is120Wurf = true;
                                break;
                            }
                        }

                        foreach ($spielberichtData as $row) {
                            if (!is_array($row))
                                continue;
                            $colValues = array_values($row);
                            $colCount = count($colValues);

                            // Kommentarzeile abfangen
                            $isCommentRow = false;
                            $possibleComment = '';
                            $rawRowHtml = implode(' ', $colValues);

                            if ($colCount > 0) {
                                foreach ($colValues as $col) {
                                    $text = trim(strip_tags(str_replace(['<br>', '<br/>', '&nbsp;'], ' ', (string) $col)));
                                    if (strlen($text) > 2 && stripos($text, 'undefined') === false) {
                                        if (strlen($text) > strlen($possibleComment)) {
                                            $possibleComment = $text;
                                        }
                                    }
                                }
                            }

                            if (stripos($rawRowHtml, 'colspan') !== false && strlen($possibleComment) > 10) {
                                $isCommentRow = true;
                            } elseif (preg_match('/(für.*?nach.*?Wurf|Auswechslung)/i', $possibleComment)) {
                                $isCommentRow = true;
                            }

                            if ($isCommentRow) {
                                if (preg_match('/[a-zA-Z]/', $possibleComment) && stripos($possibleComment, 'Gesamt') === false) {
                                    if (strpos($spielKommentar, $possibleComment) === false) {
                                        $spielKommentar .= ($spielKommentar ? "\n" : '') . $possibleComment;
                                    }
                                }
                                continue;
                            }

                            if ($colCount < 10)
                                continue;

                            $firstCol = parsePlayerNameAndStats($colValues[0] ?? '');
                            $lastCol = parsePlayerNameAndStats($colValues[$colCount - 1] ?? '');
                            $heim_name = trim(str_ireplace('undefined', '', $firstCol['name']));
                            $gast_name = trim(str_ireplace('undefined', '', $lastCol['name']));
                            $kegel_heim_val = cleanSportwinnerHtml($is120Wurf ? ($colValues[5] ?? '') : ($colValues[4] ?? ''));

                            // Summenzeile abfangen
                            $isSum = (stripos($heim_name, 'Gesamt') !== false || stripos($gast_name, 'Gesamt') !== false || (empty($heim_name) && (int) $kegel_heim_val > 500));
                            if ($isSum) {
                                if ($is120Wurf) {
                                    $summenZeile = [
                                        'heim_kegel' => cleanSportwinnerHtml($colValues[5] ?? ''),
                                        'gast_kegel' => cleanSportwinnerHtml($colValues[$colCount - 6] ?? '')
                                    ];
                                } else {
                                    $summenZeile = [
                                        'heim_kegel' => cleanSportwinnerHtml($colValues[4] ?? ''),
                                        'gast_kegel' => cleanSportwinnerHtml($colValues[$colCount - 5] ?? '')
                                    ];
                                }
                            }
                        }
                    }

                    // Holz im Header aktualisieren, falls wir die Summen haben
                    if ($summenZeile !== null) {
                        if ($holz_heim === '-' || $holz_heim === '' || $holz_heim === '0')
                            $holz_heim = $summenZeile['heim_kegel'];
                        if ($holz_gast === '-' || $holz_gast === '' || $holz_gast === '0')
                            $holz_gast = $summenZeile['gast_kegel'];
                    }

                    // =================================================================
                    // ENDE FEHLENDER BLOCK
                    // =================================================================
    
                    // Heim- oder Auswärtsspiel?
                    $isHeimspiel = (stripos($heim, 'Eisingen') !== false || stripos($heim, 'Nüünerkiller') !== false);
                    $spielOrtLabel = $isHeimspiel
                        ? "<span style='background: #e67e22; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; letter-spacing: 0.5px;'>HEIMTSPIEL</span>"
                        : "<span style='background: #3498db; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; letter-spacing: 0.5px;'>AUSWÄRTSSPIEL</span>";

                    // Kopfbereich: Zusammenfassung des Spiels
                    echo "<div style='text-align: center; margin-bottom: 25px;'>";
                    echo "<div style='margin-bottom: 10px;'>" . $spielOrtLabel . "</div>";
                    echo "<div style='font-size: 0.95rem; color: #666; margin-bottom: 12px;'><i class='far fa-calendar'></i> $datum, $zeit Uhr</div>";
                    echo "<div style='display: flex; justify-content: center; align-items: center; gap: 15px; font-size: 1.2rem; font-weight: bold; flex-wrap: wrap;'>";
                    echo "<div style='flex: 1; text-align: right; min-width: 120px; font-weight: normal; color: #333;'>" . $heim . "</div>";
                    echo "<div style='background: var(--sidebar-color); color: white; padding: 12px 25px; border-radius: 10px; text-align: center; min-width: 160px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>" . $punkte_heim . " : " . $punkte_gast . "<br><small style='font-size: 0.85rem; font-weight: normal; opacity: 0.9;'>" . $holz_heim . " : " . $holz_gast . " Holz</small></div>";
                    echo "<div style='flex: 1; text-align: left; min-width: 120px; font-weight: normal; color: #333;'>" . $gast . "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo "<div style='text-align: center; margin-top: 10px; margin-bottom: 20px;'>";
                    echo "<button id='toggle-mehr-infos-btn' class='btn btn-secondary' onclick='toggleMehrInfos()' style='cursor: pointer;'><i class='fas fa-chevron-down'></i> Mehr Infos</button>";
                    echo "</div>";

                    echo "<div id='mehr-infos-container' style='display: none;'>";

                    if (is_array($spielberichtData) && count($spielberichtData) > 0) {
                        echo "<div style='overflow-x: auto;'>";
                        echo "<table class='admin-table' style='width: 100%; min-width: 800px; font-size: 0.85rem; text-align: center;'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th colspan='" . ($is120Wurf ? "7" : "6") . "' style='text-align: left; background-color: var(--sidebar-color);'>Heim</th>";
                        echo "<th colspan='2' style='text-align: center; background-color: #333;'>MP</th>";
                        echo "<th colspan='" . ($is120Wurf ? "7" : "6") . "' style='text-align: right; background-color: var(--sidebar-color);'>Gast</th>";
                        echo "</tr>";
                        echo "<tr style='background-color: #f3f4f6; color: #333;'>";
                        if ($is120Wurf) {
                            echo "<th style='text-align: left; color: #333;'>Spieler</th><th style='color: #333;'>B1</th><th style='color: #333;'>B2</th><th style='color: #333;'>B3</th><th style='color: #333;'>B4</th><th style='color: #333;'>Gesamt</th><th style='color: #333;'>SP</th>";
                        } else {
                            echo "<th style='text-align: left; color: #333;'>Spieler</th><th style='color: #333;'>Volle</th><th style='color: #333;'>Abr.</th><th style='color: #333;'>Fehl</th><th style='color: #333;'>Gesamt</th><th style='color: #333;'>SP</th>";
                        }
                        echo "<th style='color: #333; border-left: 2px solid #ddd;'>H</th><th style='color: #333; border-right: 2px solid #ddd;'>G</th>";
                        if ($is120Wurf) {
                            echo "<th style='color: #333;'>SP</th><th style='color: #333;'>Gesamt</th><th style='color: #333;'>B1</th><th style='color: #333;'>B2</th><th style='color: #333;'>B3</th><th style='color: #333;'>B4</th><th style='text-align: right; color: #333;'>Spieler</th>";
                        } else {
                            echo "<th style='color: #333;'>SP</th><th style='color: #333;'>Gesamt</th><th style='color: #333;'>Fehl</th><th style='color: #333;'>Abr.</th><th style='color: #333;'>Volle</th><th style='text-align: right; color: #333;'>Spieler</th>";
                        }
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        // WICHTIG: $spielKommentar darf hier nicht geleert werden, sonst verschwinden unsere Daten!
                        $summenZeile = null;

                        foreach ($spielberichtData as $index => $row) {
                            if (!is_array($row))
                                continue;
                            $colCount = count($row);

                            // --- NEU: Aggressive und zuverlässige Kommentar-Erkennung ---
                            // Verhindert, dass Zeilenumbrüche Wörter zusammenkleben
                            $isCommentRow = false;
                            $possibleComment = '';
                            $colValues = array_values($row); // Sicherstellen, dass wir einen numerischen Index haben
                            $rawRowHtml = implode(' ', $colValues);

                            if (count($colValues) > 0) {
                                foreach ($colValues as $col) {
                                    $text = trim(strip_tags(str_replace(['<br>', '<br/>', '&nbsp;'], ' ', (string) $col)));
                                    if (strlen($text) > 2 && stripos($text, 'undefined') === false) {
                                        if (strlen($text) > strlen($possibleComment)) {
                                            $possibleComment = $text;
                                        }
                                    }
                                }
                            }

                            if (stripos($rawRowHtml, 'colspan') !== false && strlen($possibleComment) > 10) {
                                $isCommentRow = true;
                            } elseif (preg_match('/(für.*?nach.*?Wurf|Auswechslung)/i', $possibleComment)) {
                                $isCommentRow = true;
                            }

                            if ($isCommentRow) {
                                if (preg_match('/[a-zA-Z]/', $possibleComment) && stripos($possibleComment, 'Gesamt') === false) {
                                    if (strpos($spielKommentar, $possibleComment) === false) {
                                        $spielKommentar .= ($spielKommentar ? "\n" : '') . $possibleComment;
                                    }
                                }
                                continue;
                            }

                            if ($colCount < 10)
                                continue; // Zu wenig Daten für ein Match
    
                            $heimData = parsePlayerNameAndStats($row[0] ?? '');
                            $gastData = parsePlayerNameAndStats($row[$colCount - 1] ?? '');
                            $heim_name = trim(str_ireplace('undefined', '', $heimData['name']));
                            $gast_name = trim(str_ireplace('undefined', '', $gastData['name']));

                            $kegel_heim_val = cleanSportwinnerHtml($is120Wurf ? ($row[5] ?? '') : ($row[4] ?? ''));

                            // 2. Summen-Zeile abfangen
                            $isSum = (stripos($heim_name, 'Gesamt') !== false || stripos($gast_name, 'Gesamt') !== false || (empty($heim_name) && (int) $kegel_heim_val > 500));
                            if ($isSum) {
                                if ($is120Wurf) {
                                    $summenZeile = [
                                        'heim_kegel' => cleanSportwinnerHtml($row[5] ?? ''),
                                        'heim_sp' => cleanSportwinnerHtml($row[6] ?? ''),
                                        'heim_mp' => cleanSportwinnerHtml($row[7] ?? ''),
                                        'gast_mp' => cleanSportwinnerHtml($row[$colCount - 8] ?? ''),
                                        'gast_sp' => cleanSportwinnerHtml($row[$colCount - 7] ?? ''),
                                        'gast_kegel' => cleanSportwinnerHtml($row[$colCount - 6] ?? '')
                                    ];
                                } else {
                                    $summenZeile = [
                                        'heim_kegel' => cleanSportwinnerHtml($row[4] ?? ''),
                                        'heim_sp' => cleanSportwinnerHtml($row[5] ?? ''),
                                        'heim_mp' => cleanSportwinnerHtml($row[6] ?? ''),
                                        'gast_mp' => cleanSportwinnerHtml($row[$colCount - 7] ?? ''),
                                        'gast_sp' => cleanSportwinnerHtml($row[$colCount - 6] ?? ''),
                                        'gast_kegel' => cleanSportwinnerHtml($row[$colCount - 5] ?? '')
                                    ];
                                }
                                continue; // Nicht als normale Spielerzeile rendern
                            }

                            // Leere Platzhalterzeilen überspringen
                            if (empty($heim_name) && empty($gast_name))
                                continue;

                            $rowId = "details_row_" . $index;

                            $heim_gesamt = cleanSportwinnerHtml($is120Wurf ? ($row[5] ?? '') : ($row[4] ?? ''));
                            $gast_gesamt = cleanSportwinnerHtml($is120Wurf ? ($row[$colCount - 6] ?? '') : ($row[$colCount - 5] ?? ''));

                            $heim_gesamt_style = "font-weight: bold;";
                            if ((int) $heim_gesamt >= 550)
                                $heim_gesamt_style .= " color: #e74c3c;";
                            elseif ((int) $heim_gesamt >= 500)
                                $heim_gesamt_style .= " color: #e67e22;";

                            $gast_gesamt_style = "font-weight: bold;";
                            if ((int) $gast_gesamt >= 550)
                                $gast_gesamt_style .= " color: #e74c3c;";
                            elseif ((int) $gast_gesamt >= 500)
                                $gast_gesamt_style .= " color: #e67e22;";

                            echo "<tr>";

                            // Heim Spieler
                            echo "<td style='text-align: left; white-space: nowrap;'>";
                            if ($heimData['hasStats']) {
                                echo "<i class='fa fa-plus-circle' style='cursor:pointer; color:var(--sidebar-color); margin-right:8px;' onclick='toggleDetails(\"{$rowId}\")'></i>";
                            }
                            echo $heim_name . "</td>";

                            if ($is120Wurf) {
                                $mp_heim = cleanSportwinnerHtml($row[7] ?? '');
                                $mp_gast = cleanSportwinnerHtml($row[$colCount - 8] ?? '');
                                $mp_heim_color = ((float) $mp_heim > (float) $mp_gast) ? '#27ae60' : (((float) $mp_heim < (float) $mp_gast) ? '#c0392b' : '#333');
                                $mp_gast_color = ((float) $mp_gast > (float) $mp_heim) ? '#27ae60' : (((float) $mp_gast < (float) $mp_heim) ? '#c0392b' : '#333');

                                echo "<td>" . cleanSportwinnerHtml($row[1] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[2] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[3] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[4] ?? '') . "</td>";
                                echo "<td style='{$heim_gesamt_style}'>" . $heim_gesamt . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[6] ?? '') . "</td>";

                                echo "<td style='font-weight: bold; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $mp_heim . "</td>";
                                echo "<td style='font-weight: bold; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $mp_gast . "</td>";

                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 7] ?? '') . "</td>";
                                echo "<td style='{$gast_gesamt_style}'>" . $gast_gesamt . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 5] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 4] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 3] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 2] ?? '') . "</td>";
                            } else {
                                $mp_heim = cleanSportwinnerHtml($row[6] ?? '');
                                $mp_gast = cleanSportwinnerHtml($row[$colCount - 7] ?? '');
                                $mp_heim_color = ((float) $mp_heim > (float) $mp_gast) ? '#27ae60' : (((float) $mp_heim < (float) $mp_gast) ? '#c0392b' : '#333');
                                $mp_gast_color = ((float) $mp_gast > (float) $mp_heim) ? '#27ae60' : (((float) $mp_gast < (float) $mp_heim) ? '#c0392b' : '#333');

                                echo "<td>" . cleanSportwinnerHtml($row[1] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[2] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[3] ?? '') . "</td>";
                                echo "<td style='{$heim_gesamt_style}'>" . $heim_gesamt . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[5] ?? '') . "</td>";

                                echo "<td style='font-weight: bold; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $mp_heim . "</td>";
                                echo "<td style='font-weight: bold; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $mp_gast . "</td>";

                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 6] ?? '') . "</td>";
                                echo "<td style='{$gast_gesamt_style}'>" . $gast_gesamt . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 4] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 3] ?? '') . "</td>";
                                echo "<td>" . cleanSportwinnerHtml($row[$colCount - 2] ?? '') . "</td>";
                            }

                            // Gast Spieler
                            echo "<td style='text-align: right; white-space: nowrap;'>" . $gast_name;
                            if ($gastData['hasStats']) {
                                echo "<i class='fa fa-plus-circle' style='cursor:pointer; color:var(--sidebar-color); margin-left:8px;' onclick='toggleDetails(\"{$rowId}\")'></i>";
                            }
                            echo "</td></tr>";

                            // Ausklappbare Detail-Reihe
                            if ($heimData['hasStats'] || $gastData['hasStats']) {
                                echo "<tr id='{$rowId}' style='display:none; background-color:#fafafa; border-bottom: 2px solid #eee;'>";
                                echo "<td colspan='7' style='text-align:left; padding-left:30px; font-size:0.8rem; color:#555;'>";
                                if ($heimData['hasStats']) {
                                    echo "Volle: <strong>{$heimData['stats']['V']}</strong> &nbsp;|&nbsp; Abr.: <strong>{$heimData['stats']['A']}</strong> &nbsp;|&nbsp; Fehl: <strong>{$heimData['stats']['F']}</strong>";
                                }
                                echo "</td><td colspan='2' style='background-color:#f9f9f9;'></td><td colspan='7' style='text-align:right; padding-right:30px; font-size:0.8rem; color:#555;'>";
                                if ($gastData['hasStats']) {
                                    echo "Volle: <strong>{$gastData['stats']['V']}</strong> &nbsp;|&nbsp; Abr.: <strong>{$gastData['stats']['A']}</strong> &nbsp;|&nbsp; Fehl: <strong>{$gastData['stats']['F']}</strong>";
                                }
                                echo "</td></tr>";
                            }
                        }

                        // Summenzeile am Ende der Tabelle ausgeben
                        if ($summenZeile !== null) {
                            $mp_heim_color = ((float) $summenZeile['heim_mp'] > (float) $summenZeile['gast_mp']) ? '#27ae60' : (((float) $summenZeile['heim_mp'] < (float) $summenZeile['gast_mp']) ? '#c0392b' : '#333');
                            $mp_gast_color = ((float) $summenZeile['gast_mp'] > (float) $summenZeile['heim_mp']) ? '#27ae60' : (((float) $summenZeile['gast_mp'] < (float) $summenZeile['heim_mp']) ? '#c0392b' : '#333');

                            echo "<tr style='font-weight: bold; background-color: rgba(230, 126, 34, 0.15); border-top: 2px solid var(--sidebar-color);'>";
                            if ($is120Wurf) {
                                echo "<td colspan='5' style='text-align: right; text-transform: uppercase;'>GESAMT:</td>";
                                echo "<td style='font-size: 1.1rem;'>" . $summenZeile['heim_kegel'] . "</td>";
                                echo "<td>" . $summenZeile['heim_sp'] . "</td>";
                                echo "<td style='font-size: 1.1rem; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $summenZeile['heim_mp'] . "</td>";
                                echo "<td style='font-size: 1.1rem; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $summenZeile['gast_mp'] . "</td>";
                                echo "<td>" . $summenZeile['gast_sp'] . "</td>";
                                echo "<td style='font-size: 1.1rem;'>" . $summenZeile['gast_kegel'] . "</td>";
                                echo "<td colspan='5'></td>";
                            } else {
                                echo "<td colspan='4' style='text-align: right; text-transform: uppercase;'>GESAMT:</td>";
                                echo "<td style='font-size: 1.1rem;'>" . $summenZeile['heim_kegel'] . "</td>";
                                echo "<td>" . $summenZeile['heim_sp'] . "</td>";
                                echo "<td style='font-size: 1.1rem; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $summenZeile['heim_mp'] . "</td>";
                                echo "<td style='font-size: 1.1rem; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $summenZeile['gast_mp'] . "</td>";
                                echo "<td>" . $summenZeile['gast_sp'] . "</td>";
                                echo "<td style='font-size: 1.1rem;'>" . $summenZeile['gast_kegel'] . "</td>";
                                echo "<td colspan='4'></td>";
                            }
                            echo "</tr>";
                        }
                        echo "</tbody></table></div>";

                        // Kommentar unter der Tabelle anzeigen
                        if (!empty($spielKommentar)) {
                            echo "<div style='margin-top: 25px; padding: 20px; background-color: #ffffff; border-left: 5px solid var(--sidebar-color); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06), 0 15px 25px -5px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05); text-align: left;'>";
                            echo "<h4 style='color: var(--sidebar-color); margin-top: 0; margin-bottom: 12px; font-size: 1.1rem;'><i class='fas fa-info-circle'></i> Auswechslungen & Infos</h4>";
                            echo "<div style='line-height: 1.6; font-size: 0.95rem; color: #333;'>" . nl2br(htmlspecialchars($spielKommentar)) . "</div>";
                            echo "</div>";
                        }

                    } else {
                        echo "<div style='text-align: center; padding: 25px; background-color: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc; margin-top: 20px;'>";
                        echo "<i class='fas fa-lock' style='font-size: 2rem; color: #95a5a6; margin-bottom: 10px;'></i>";
                        echo "<p style='color: #555; margin: 0;'><strong>Spielbericht noch nicht freigegeben</strong></p>";
                        echo "<p style='color: #777; font-size: 0.9rem; margin-top: 5px;'>Sportwinner blockiert aktuell die Datenabfrage (Fehlercode -1). Dies passiert meistens bei <strong>vorverlegten Spielen</strong>, die offiziell in der Zukunft liegen, oder wenn der Bericht vom Verband noch nicht bestätigt wurde.</p>";
                        echo "</div>";
                    }

                    echo "</div>"; // Ende mehr-infos-container
    
                    // Das JavaScript für die ausklappbaren Bereiche
                    echo "<script>
                    function toggleDetails(rowId) {
                        var row = document.getElementById(rowId);
                        row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
                    }
                    function toggleMehrInfos() {
                        var container = document.getElementById('mehr-infos-container');
                        var btn = document.getElementById('toggle-mehr-infos-btn');
                        if (container.style.display === 'none' || container.style.display === '') {
                            container.style.display = 'block';
                            btn.innerHTML = '<i class=\"fas fa-chevron-up\"></i> Weniger Infos';
                        } else {
                            container.style.display = 'none';
                            btn.innerHTML = '<i class=\"fas fa-chevron-down\"></i> Mehr Infos';
                        }
                    }
                    </script>";
                } else {
                    echo "<p style='text-align: center; color: #e74c3c; margin-top: 20px;'>Für diesen Spieltag konnte kein Spiel von SKV Eisingen / Nüünerkiller gefunden werden.</p>";
                }

                echo "<p style='margin-top: 15px; font-size: 0.8rem; color: #888; text-align: right;'>Daten bereitgestellt von Sportwinner</p>";

                echo "</div>";
            }

            // =================================================================
            // HIER WAR DER FEHLER: Dieser Block hat gefehlt!
            // C) Die hochgeladenen Bildergalerie abfragen und in $bilder speichern
            // =================================================================
            $sqlBilder = "SELECT bild_pfad FROM news_bilder WHERE news_id = :id AND is_deleted = 0";
            $stmtBilder = $pdo->prepare($sqlBilder);
            $stmtBilder->execute([':id' => $news_id]);
            $bilder = $stmtBilder->fetchAll(PDO::FETCH_COLUMN);

            // D) Wenn es Bilder gibt, rendern wir sie als klickbare Thumbnails
            if (count($bilder) > 0) {
                echo "<hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>";
                echo "<h3>Galerie (zum Vergrößern klicken)</h3>";

                echo "<div class='news-gallery'>";
                foreach ($bilder as $pfad) {
                    echo "<img src='" . htmlspecialchars($pfad) . "' alt='Bild zur News' class='news-thumbnail' loading='lazy'>";
                }
                echo "</div>";
            }

            echo "</article>";

        } else {
            echo "<div class='content-tile alert-error'>Diese News existiert leider nicht (mehr).</div>";
        }

    } catch (PDOException $e) {
        echo "<div class='content-tile alert-error'>Fehler beim Laden der News: " . $e->getMessage() . "</div>";
    }
    ?>

</main>

<?php
require_once __DIR__ . '/../../templates/footer.php';
?>