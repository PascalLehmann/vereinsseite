<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../db.php';

// Lade die Einstellungen aus der Datenbank (Saison & Liga)
$saison_id = '';
$liga_id = '';
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('sportwinner_saison_id', 'sportwinner_liga_id')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $saison_id = $settings['sportwinner_saison_id'] ?? '';
    $liga_id = $settings['sportwinner_liga_id'] ?? '';
} catch (PDOException $e) {
    // Ignorieren, falls Settings fehlen
}

// Prüfen, ob HEUTE ein Spieltag im Kalender steht
$heute = date('Y-m-d');
$spieltag_nr = 0;
$termin_titel = '';

try {
    // Sucht nach Terminen von heute, bei denen ein Spieltag eingetragen wurde (> 0)
    $stmt = $pdo->prepare("SELECT titel, sw_spieltag FROM termine WHERE DATE(termin_datum) = :heute AND sw_spieltag > 0 LIMIT 1");
    $stmt->execute([':heute' => $heute]);
    $termin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($termin) {
        $spieltag_nr = (int) $termin['sw_spieltag'];
        $termin_titel = $termin['titel'];
    }
} catch (PDOException $e) {
    // Fehler abfangen, falls das Datenbankfeld noch nicht existiert
}

$pageTitle = "🔴 Liveview - Spieltag";
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <h1 style="color: #e74c3c;"><i class="fas fa-broadcast-tower fa-beat" style="margin-right: 10px;"></i>Liveview</h1>

    <div class="content-tile">
        <?php if ($spieltag_nr <= 0): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-calendar-times" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">Aktuell findet kein Spiel statt</h3>
                <p style="color: #888; font-size: 1rem;">Der Liveview ist nur an Tagen aktiv, an denen laut Kalender ein
                    Spieltag angesetzt ist.</p>
                <a href="tabelle.php" class="btn btn-primary" style="margin-top: 20px;">Zur aktuellen Tabelle</a>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-bottom: 30px;">
                <h3 style="color: var(--sidebar-color); margin-bottom: 5px;"><?= htmlspecialchars($termin_titel) ?></h3>
                <div
                    style="display: inline-block; background: #e74c3c; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; margin-bottom: 15px;">
                    <i class="fas fa-circle fa-fade"
                        style="font-size: 0.6rem; vertical-align: middle; margin-right: 5px;"></i> LIVE DATEN
                </div>
            </div>

            <?php
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

            // 1. Spielplan abrufen
            $spielplanResponse = fetchSportwinnerAPI([
                'command' => 'GetSpielplan',
                'id_saison' => $saison_id,
                'id_liga' => $liga_id
            ]);
            $spielplanData = $spielplanResponse['data'] ?? $spielplanResponse;

            // 2. Unser Spiel suchen
            $unserSpiel = null;
            $gesuchter_spieltag = (int) $spieltag_nr;
            $match_counter = 0;

            if (is_array($spielplanData)) {
                foreach ($spielplanData as $match) {
                    if (!is_array($match))
                        continue;
                    $rowString = implode(' ', $match);

                    if (stripos($rowString, 'Eisingen') !== false || stripos($rowString, 'Nüünerkiller') !== false) {
                        $match_counter++;
                        $row_spieltag_raw = cleanSportwinnerHtml($match[0] ?? '');
                        preg_match('/\d+/', $row_spieltag_raw, $m);
                        $row_spieltag = isset($m[0]) ? (int) $m[0] : 0;

                        if ($row_spieltag === $gesuchter_spieltag || $match_counter === $gesuchter_spieltag) {
                            $unserSpiel = $match;
                            break;
                        }
                    }
                }
            }

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

                $raw_id = trim(strip_tags($unserSpiel[1] ?? ''));
                if (isset($unserSpiel[4]) && isset($unserSpiel[5]) && preg_match('/^\d{5,}$/', $raw_id)) {
                    $id_spiel = (int) $raw_id;
                    $dz_parts = explode('-', cleanSportwinnerHtml($unserSpiel[3] ?? ''));
                    $datum = trim($dz_parts[0] ?? '');
                    $zeit = trim($dz_parts[1] ?? '');
                    $heim = cleanSportwinnerHtml($unserSpiel[4]);
                    $gast = cleanSportwinnerHtml($unserSpiel[5]);
                    $punkte_heim = cleanSportwinnerHtml($unserSpiel[7] ?? '-');
                    $punkte_gast = cleanSportwinnerHtml($unserSpiel[8] ?? '-');
                } else {
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

                // Detail-Daten abfragen
                $api_GetSpielbericht = fetchSportwinnerAPI(['command' => 'GetSpielbericht', 'id_spiel' => $id_spiel]);
                $api_GetSpielerInfo = fetchSportwinnerAPI(['command' => 'GetSpielerInfo', 'id_saison' => $saison_id, 'id_spiel' => $id_spiel, 'wertung' => '1']);
                $api_GetBericht = fetchSportwinnerAPI(['command' => 'GetBericht', 'id_spiel' => $id_spiel]);

                $api_Spieltage = fetchSportwinnerAPI(['command' => 'GetSpieltagArray', 'id_saison' => $saison_id, 'id_liga' => $liga_id, 'id_bezirk' => '0']);
                $stData = $api_Spieltage['data'] ?? (is_array($api_Spieltage) ? $api_Spieltage : []);
                $internal_spieltag_id = '';
                foreach ($stData as $st) {
                    $stLabel = isset($st[1]) ? trim((string) $st[1]) : '';
                    $stVal = isset($st[0]) ? trim((string) $st[0]) : '';
                    if ($stLabel === (string) $spieltag_nr || strpos($stLabel, $spieltag_nr . '.') === 0 || strpos($stLabel, $spieltag_nr . ' ') === 0 || (int) $stLabel === (int) $spieltag_nr) {
                        $internal_spieltag_id = $stVal;
                        break;
                    }
                }
                if (empty($internal_spieltag_id))
                    $internal_spieltag_id = (string) $spieltag_nr;

                $api_GetSpiel = fetchSportwinnerAPI(['command' => 'GetSpiel', 'id_saison' => $saison_id, 'id_klub' => '0', 'id_bezirk' => '0', 'id_liga' => $liga_id, 'id_spieltag' => $internal_spieltag_id, 'favorit' => '', 'art_bezirk' => '1', 'art_liga' => '0', 'art_spieltag' => '2']);

                $data_GetSpielbericht = $api_GetSpielbericht['data'] ?? (is_array($api_GetSpielbericht) ? $api_GetSpielbericht : []);
                $data_GetSpielerInfo = $api_GetSpielerInfo['data'] ?? (is_array($api_GetSpielerInfo) ? $api_GetSpielerInfo : []);
                $data_GetBericht = $api_GetBericht['data'] ?? (is_array($api_GetBericht) ? $api_GetBericht : []);
                $data_GetSpiel = $api_GetSpiel['data'] ?? (is_array($api_GetSpiel) ? $api_GetSpiel : []);

                $spielberichtData = [];
                foreach ([$data_GetSpielbericht, $data_GetSpielerInfo, $data_GetBericht] as $arr) {
                    if (is_array($arr) && count($arr) > count($spielberichtData))
                        $spielberichtData = $arr;
                }
                if (empty($spielberichtData)) {
                    $fallback = fetchSportwinnerAPI(['command' => 'GetSpielbericht', 'id' => $id_spiel]);
                    $spielberichtData = $fallback['data'] ?? (is_array($fallback) ? $fallback : []);
                }

                $is120Wurf = false;
                $summenZeile = null;
                $spielKommentar = '';

                foreach ([$data_GetSpiel, $spielplanData] as $arr) {
                    if (!empty($arr) && is_array($arr)) {
                        foreach ($arr as $matchRow) {
                            $rowId0 = isset($matchRow[0]) ? (int) cleanSportwinnerHtml($matchRow[0]) : 0;
                            $rowId1 = isset($matchRow[1]) ? (int) cleanSportwinnerHtml($matchRow[1]) : 0;
                            if ($rowId0 === $id_spiel || $rowId1 === $id_spiel) {
                                for ($c = 8; $c <= 12; $c++) {
                                    if (!empty($matchRow[$c])) {
                                        $kommentarRaw = cleanSportwinnerHtml($matchRow[$c]);
                                        if ($kommentarRaw !== '' && stripos($kommentarRaw, 'undefined') === false) {
                                            if (preg_match('/(für.*?nach|Wurf|Auswechslung|;)/i', $kommentarRaw) || strlen($kommentarRaw) > 25) {
                                                $kommentarTeile = array_map('trim', explode(';', $kommentarRaw));
                                                $formatted = implode("\n", array_filter($kommentarTeile));
                                                if (strpos($spielKommentar, $formatted) === false)
                                                    $spielKommentar .= ($spielKommentar ? "\n" : "") . $formatted;
                                                break 3;
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

                        $heimData = parsePlayerNameAndStats($colValues[0] ?? '');
                        $gastData = parsePlayerNameAndStats($colValues[$colCount - 1] ?? '');
                        $heim_name = trim(str_ireplace('undefined', '', $heimData['name']));
                        $gast_name = trim(str_ireplace('undefined', '', $gastData['name']));
                        $kegel_heim_val = cleanSportwinnerHtml($is120Wurf ? ($colValues[5] ?? '') : ($colValues[4] ?? ''));

                        if (stripos($heim_name, 'Gesamt') !== false || stripos($gast_name, 'Gesamt') !== false || (empty($heim_name) && (int) $kegel_heim_val > 500)) {
                            if ($is120Wurf) {
                                $summenZeile = ['heim_kegel' => cleanSportwinnerHtml($colValues[5] ?? ''), 'heim_sp' => cleanSportwinnerHtml($colValues[6] ?? ''), 'heim_mp' => cleanSportwinnerHtml($colValues[7] ?? ''), 'gast_mp' => cleanSportwinnerHtml($colValues[$colCount - 8] ?? ''), 'gast_sp' => cleanSportwinnerHtml($colValues[$colCount - 7] ?? ''), 'gast_kegel' => cleanSportwinnerHtml($colValues[$colCount - 6] ?? '')];
                            } else {
                                $summenZeile = ['heim_kegel' => cleanSportwinnerHtml($colValues[4] ?? ''), 'heim_sp' => cleanSportwinnerHtml($colValues[5] ?? ''), 'heim_mp' => cleanSportwinnerHtml($colValues[6] ?? ''), 'gast_mp' => cleanSportwinnerHtml($colValues[$colCount - 7] ?? ''), 'gast_sp' => cleanSportwinnerHtml($colValues[$colCount - 6] ?? ''), 'gast_kegel' => cleanSportwinnerHtml($colValues[$colCount - 5] ?? '')];
                            }
                        }
                    }
                }

                if ($summenZeile !== null) {
                    if ($holz_heim === '-' || $holz_heim === '' || $holz_heim === '0')
                        $holz_heim = $summenZeile['heim_kegel'];
                    if ($holz_gast === '-' || $holz_gast === '' || $holz_gast === '0')
                        $holz_gast = $summenZeile['gast_kegel'];
                }

                $isHeimspiel = (stripos($heim, 'Eisingen') !== false || stripos($heim, 'Nüünerkiller') !== false);
                $spielOrtLabel = $isHeimspiel
                    ? "<span style='background: #e67e22; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; letter-spacing: 0.5px;'>HEIMTSPIEL</span>"
                    : "<span style='background: #3498db; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; letter-spacing: 0.5px;'>AUSWÄRTSSPIEL</span>";

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
                    echo "<thead><tr>";
                    echo "<th colspan='" . ($is120Wurf ? "7" : "6") . "' style='text-align: left; background-color: var(--sidebar-color);'>Heim</th>";
                    echo "<th colspan='2' style='text-align: center; background-color: #333;'>MP</th>";
                    echo "<th colspan='" . ($is120Wurf ? "7" : "6") . "' style='text-align: right; background-color: var(--sidebar-color);'>Gast</th>";
                    echo "</tr><tr style='background-color: #f3f4f6; color: #333;'>";
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
                    echo "</tr></thead><tbody>";

                    $summenZeile = null;
                    foreach ($spielberichtData as $index => $row) {
                        if (!is_array($row))
                            continue;
                        $colCount = count($row);
                        $colValues = array_values($row);

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

                        if ($isCommentRow)
                            continue;

                        if ($colCount < 10)
                            continue;
                        $heimData = parsePlayerNameAndStats($row[0] ?? '');
                        $gastData = parsePlayerNameAndStats($row[$colCount - 1] ?? '');
                        $heim_name = trim(str_ireplace('undefined', '', $heimData['name']));
                        $gast_name = trim(str_ireplace('undefined', '', $gastData['name']));
                        $kegel_heim_val = cleanSportwinnerHtml($is120Wurf ? ($row[5] ?? '') : ($row[4] ?? ''));

                        if (stripos($heim_name, 'Gesamt') !== false || stripos($gast_name, 'Gesamt') !== false || (empty($heim_name) && (int) $kegel_heim_val > 500)) {
                            if ($is120Wurf) {
                                $summenZeile = ['heim_kegel' => cleanSportwinnerHtml($row[5] ?? ''), 'heim_sp' => cleanSportwinnerHtml($row[6] ?? ''), 'heim_mp' => cleanSportwinnerHtml($row[7] ?? ''), 'gast_mp' => cleanSportwinnerHtml($row[$colCount - 8] ?? ''), 'gast_sp' => cleanSportwinnerHtml($row[$colCount - 7] ?? ''), 'gast_kegel' => cleanSportwinnerHtml($row[$colCount - 6] ?? '')];
                            } else {
                                $summenZeile = ['heim_kegel' => cleanSportwinnerHtml($row[4] ?? ''), 'heim_sp' => cleanSportwinnerHtml($row[5] ?? ''), 'heim_mp' => cleanSportwinnerHtml($row[6] ?? ''), 'gast_mp' => cleanSportwinnerHtml($row[$colCount - 7] ?? ''), 'gast_sp' => cleanSportwinnerHtml($row[$colCount - 6] ?? ''), 'gast_kegel' => cleanSportwinnerHtml($row[$colCount - 5] ?? '')];
                            }
                            continue;
                        }

                        if (empty($heim_name) && empty($gast_name))
                            continue;

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

                        echo "<tr><td style='text-align: left; white-space: nowrap;'>" . $heim_name . "</td>";
                        if ($is120Wurf) {
                            $mp_heim = cleanSportwinnerHtml($row[7] ?? '');
                            $mp_gast = cleanSportwinnerHtml($row[$colCount - 8] ?? '');
                            $mp_heim_color = ((float) $mp_heim > (float) $mp_gast) ? '#27ae60' : (((float) $mp_heim < (float) $mp_gast) ? '#c0392b' : '#333');
                            $mp_gast_color = ((float) $mp_gast > (float) $mp_heim) ? '#27ae60' : (((float) $mp_gast < (float) $mp_heim) ? '#c0392b' : '#333');
                            echo "<td>" . cleanSportwinnerHtml($row[1] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[2] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[3] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[4] ?? '') . "</td><td style='{$heim_gesamt_style}'>" . $heim_gesamt . "</td><td>" . cleanSportwinnerHtml($row[6] ?? '') . "</td>";
                            echo "<td style='font-weight: bold; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $mp_heim . "</td><td style='font-weight: bold; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $mp_gast . "</td>";
                            echo "<td>" . cleanSportwinnerHtml($row[$colCount - 7] ?? '') . "</td><td style='{$gast_gesamt_style}'>" . $gast_gesamt . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 5] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 4] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 3] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 2] ?? '') . "</td>";
                        } else {
                            $mp_heim = cleanSportwinnerHtml($row[6] ?? '');
                            $mp_gast = cleanSportwinnerHtml($row[$colCount - 7] ?? '');
                            $mp_heim_color = ((float) $mp_heim > (float) $mp_gast) ? '#27ae60' : (((float) $mp_heim < (float) $mp_gast) ? '#c0392b' : '#333');
                            $mp_gast_color = ((float) $mp_gast > (float) $mp_heim) ? '#27ae60' : (((float) $mp_gast < (float) $mp_heim) ? '#c0392b' : '#333');
                            echo "<td>" . cleanSportwinnerHtml($row[1] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[2] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[3] ?? '') . "</td><td style='{$heim_gesamt_style}'>" . $heim_gesamt . "</td><td>" . cleanSportwinnerHtml($row[5] ?? '') . "</td>";
                            echo "<td style='font-weight: bold; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $mp_heim . "</td><td style='font-weight: bold; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $mp_gast . "</td>";
                            echo "<td>" . cleanSportwinnerHtml($row[$colCount - 6] ?? '') . "</td><td style='{$gast_gesamt_style}'>" . $gast_gesamt . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 4] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 3] ?? '') . "</td><td>" . cleanSportwinnerHtml($row[$colCount - 2] ?? '') . "</td>";
                        }
                        echo "<td style='text-align: right; white-space: nowrap;'>" . $gast_name . "</td></tr>";
                    }

                    if ($summenZeile !== null) {
                        $mp_heim_color = ((float) $summenZeile['heim_mp'] > (float) $summenZeile['gast_mp']) ? '#27ae60' : (((float) $summenZeile['heim_mp'] < (float) $summenZeile['gast_mp']) ? '#c0392b' : '#333');
                        $mp_gast_color = ((float) $summenZeile['gast_mp'] > (float) $summenZeile['heim_mp']) ? '#27ae60' : (((float) $summenZeile['gast_mp'] < (float) $summenZeile['heim_mp']) ? '#c0392b' : '#333');
                        echo "<tr style='font-weight: bold; background-color: rgba(230, 126, 34, 0.15); border-top: 2px solid var(--sidebar-color);'>";
                        if ($is120Wurf) {
                            echo "<td colspan='5' style='text-align: right;'>GESAMT:</td><td style='font-size: 1.1rem;'>" . $summenZeile['heim_kegel'] . "</td><td>" . $summenZeile['heim_sp'] . "</td><td style='font-size: 1.1rem; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $summenZeile['heim_mp'] . "</td><td style='font-size: 1.1rem; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $summenZeile['gast_mp'] . "</td><td>" . $summenZeile['gast_sp'] . "</td><td style='font-size: 1.1rem;'>" . $summenZeile['gast_kegel'] . "</td><td colspan='5'></td>";
                        } else {
                            echo "<td colspan='4' style='text-align: right;'>GESAMT:</td><td style='font-size: 1.1rem;'>" . $summenZeile['heim_kegel'] . "</td><td>" . $summenZeile['heim_sp'] . "</td><td style='font-size: 1.1rem; background-color: #f9f9f9; border-left: 2px solid #ddd; color: {$mp_heim_color};'>" . $summenZeile['heim_mp'] . "</td><td style='font-size: 1.1rem; background-color: #f9f9f9; border-right: 2px solid #ddd; color: {$mp_gast_color};'>" . $summenZeile['gast_mp'] . "</td><td>" . $summenZeile['gast_sp'] . "</td><td style='font-size: 1.1rem;'>" . $summenZeile['gast_kegel'] . "</td><td colspan='4'></td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                    if (!empty($spielKommentar)) {
                        echo "<div style='margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid var(--sidebar-color); border-radius: 6px;'><strong style='color: var(--sidebar-color);'><i class='fas fa-info-circle'></i> Auswechslungen & Infos:</strong><br><span style='display:inline-block; margin-top:5px; line-height: 1.5;'>" . nl2br(htmlspecialchars($spielKommentar)) . "</span></div>";
                    }
                } else {
                    echo "<div style='text-align: center; padding: 25px; background-color: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc; margin-top: 20px;'><i class='fas fa-lock' style='font-size: 2rem; color: #95a5a6; margin-bottom: 10px;'></i><p style='color: #555; margin: 0;'><strong>Spielbericht noch nicht freigegeben / Keine Livedaten verfügbar</strong></p></div>";
                }

                echo "</div>"; // Ende mehr-infos-container
        
                // Das JavaScript für die ausklappbaren Bereiche
                echo "<script>
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
            ?>
            <script>
                // Automatischer Reload für Live-Daten (alle 60 Sekunden)
                setTimeout(function () {
                    window.location.reload();
                }, 60000);
            </script>


        <?php endif; ?>
    </div>
</main>

<?php
require_once __DIR__ . '/../../templates/footer.php';
?>