<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert: Du hast nicht die nötigen Rechte für diese Seite.");
}

require_once __DIR__ . '/../../../db.php';

$error = '';
$success = '';
$step = 1;
$teams = [];
$players = [];
$selected_team = $_POST['team_name'] ?? '';
$schnitt_typ = $_POST['schnitt_typ'] ?? '1';
$min_spiele = 1; // Hier die Mindestspiele fest im Code einstellen (z.B. auf 3 ändern, wenn gewünscht)

// --- NEU: Hilfsfunktion für die API-Kommunikation ---
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

// --- NEU: Hilfsfunktionen für sauberes HTML-Parsing ---
function cleanSportwinnerHtml($html)
{
    if (empty($html))
        return '';
    // 1. "float-right" Container entfernen (enthalten oft "NBKV" oder Altersklassen)
    $cleaned = preg_replace('/<div[^>]*class="[^"]*float-right[^"]*"[^>]*>.*?<\/div>/is', '', $html);
    // 2. HTML Tags entfernen
    $text = strip_tags($cleaned);
    // 3. Geschützte Leerzeichen durch normale ersetzen
    $text = str_replace(['&nbsp;', '&#160;', "\xC2\xA0"], ' ', $text);
    // 4. Überflüssige Leerzeichen entfernen und trimmen
    return trim(preg_replace('/\s+/', ' ', $text));
}

function extractAltersklasse($html)
{
    if (preg_match('/<div[^>]*class="[^"]*float-right[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
        return trim(strip_tags($matches[1]));
    }
    return '';
}

// --- NEU: Saisons und Ligen beim Laden der Seite abfragen ---
$saison_id = $_GET['saison_id'] ?? $_POST['saison_id'] ?? null;
$saisons = fetchSportwinnerAPI(['command' => 'GetSaisonArray']);
if (!$saison_id && !empty($saisons)) {
    $saison_id = $saisons[0][0]; // Automatisch die aktuellste Saison auswählen
}

$ligen = [];
if ($saison_id) {
    $ligen = fetchSportwinnerAPI([
        'command' => 'GetLigaArray',
        'id_saison' => $saison_id,
        'id_bezirk' => 0,
        'art' => 1, // 1 = NBKV-Ligen
        'favorit' => ''
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $liga_id = trim($_POST['liga_id'] ?? '');

    if (empty($liga_id) || !is_numeric($liga_id)) {
        $error = "Bitte wähle eine gültige Liga aus.";
    } else {
        $step = 2; // Wir haben die Liga, gehe zu Schritt 2

        // 1. Tabelle abfragen, um das Vereins-Dropdown zu füllen
        $tabelleData = fetchSportwinnerAPI([
            'command' => 'GetTabelle',
            'id_saison' => $saison_id,
            'id_liga' => $liga_id,
            'sort' => 0,
            'nr_spieltag' => 100
        ]);

        if (is_array($tabelleData)) {
            foreach ($tabelleData as $row) {
                if (isset($row[2])) {
                    $teamName = cleanSportwinnerHtml($row[2]);
                    if (!empty($teamName)) {
                        $teams[] = $teamName;
                    }
                }
            }
            usort($teams, function ($a, $b) {
                return strcasecmp($a, $b);
            });
        }


        // 2. Wenn in Schritt 2 ein Verein gewählt wurde -> Spieler abrufen!
        if (!empty($selected_team)) {
            $step = 3; // Gehe zu den Ergebnissen

            $schnittData = fetchSportwinnerAPI([
                'command' => 'GetSchnitt',
                'id_saison' => $saison_id,
                'id_klub' => 0, // Parameter hinzugefügt
                'id_liga' => $liga_id,
                'sort' => $schnitt_typ, // 1 = Heim, 2 = Auswärts
                'nr_spieltag' => 100,
                'anzahl' => $min_spiele // Wir lassen Sportwinner direkt vorfiltern!
            ]);

            if (is_array($schnittData)) {
                foreach ($schnittData as $row) {
                    $klub = cleanSportwinnerHtml($row[2] ?? '');

                    // Nur Spieler filtern, die zu unserem ausgewählten Verein gehören
                    // WICHTIG: In der Tabelle steht oft eine Nummer ("KSG Laudenbach 1"), in der Schnittliste nur "KSG Laudenbach".
                    // Wir prüfen daher auf exakte Übereinstimmung ODER ob der Vereinsname mit Leerzeichen am Anfang steht.
                    if (!empty($klub) && (strcasecmp($klub, $selected_team) === 0 || strpos($selected_team, $klub . ' ') === 0)) {
                        $spielerName = cleanSportwinnerHtml($row[1] ?? 'Unbekannt');
                        $altersklasse = extractAltersklasse($row[1] ?? '');
                        $mannschaft = cleanSportwinnerHtml($row[3] ?? '');

                        // Sportwinner packt die Spalten je nach Sortierung unterschiedlich
                        if ($schnitt_typ == '1') { // Heim
                            $spiele = (int) strip_tags((string) ($row[4] ?? 0));
                            $schnittRaw = strip_tags((string) ($row[7] ?? 0));
                        } elseif ($schnitt_typ == '2') { // Auswärts
                            $spiele = (int) strip_tags((string) ($row[5] ?? 0));
                            $schnittRaw = strip_tags((string) ($row[8] ?? 0));
                        } else { // Gesamt (0)
                            $spiele = (int) strip_tags((string) ($row[6] ?? 0));
                            $schnittRaw = strip_tags((string) ($row[9] ?? 0));
                        }

                        if (is_string($schnittRaw)) {
                            $schnittRaw = str_replace(',', '.', $schnittRaw);
                        }
                        $schnitt = (float) $schnittRaw;

                        // Nur aufnehmen, wenn der Spieler auch Spiele gemacht hat
                        if ($spiele >= $min_spiele) {
                            $players[] = [
                                'name' => $spielerName,
                                'altersklasse' => $altersklasse,
                                'mannschaft' => $mannschaft,
                                'spiele' => $spiele,
                                'schnitt' => $schnitt
                            ];
                        }
                    }
                }
                // 1. Spieler absteigend nach Schnitt sortieren, um die Platzierung zu ermitteln
                usort($players, function ($a, $b) {
                    return $b['schnitt'] <=> $a['schnitt'];
                });

                // 2. Den Rang fest speichern
                $rank = 1;
                foreach ($players as &$p) {
                    $p['rank'] = $rank++;
                }
                unset($p); // Referenz aufheben

                // 3. Spieler alphabetisch (nach Nachname) sortieren für die Ausgabe
                usort($players, function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });

                $success = "Spieler-Daten für " . htmlspecialchars($selected_team) . " erfolgreich geladen!";
            } else {
                $error = "Es konnten keine Spielerdaten geladen werden.";
            }
        }
    }
}

$pageTitle = "Gegner-Analyse";
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Gegner-Analyse (Sportwinner)</h2>
    <div class="action-bar"><a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a></div>

    <!-- SCHRITT 1: Liga-Suche -->
    <div class="content-tile" style="max-width: 800px;">
        <?php if ($error && $step == 1): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <h3>1. Liga suchen</h3>
        <p style="color: #666; margin-bottom: 15px;">Wähle die Saison und Liga aus, um die Liste der Gegner zu laden.
        </p>

        <form method="POST">
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Saison:</label>
                    <select name="saison_id" class="form-control"
                        onchange="window.location.href='?saison_id='+this.value" required>
                        <?php foreach ($saisons as $s): ?>
                            <option value="<?= htmlspecialchars($s[0]) ?>" <?= $saison_id == $s[0] ? 'selected' : '' ?>>
                                Saison <?= htmlspecialchars($s[1]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 2;">
                    <label>Liga:</label>
                    <select name="liga_id" class="form-control" required>
                        <option value="">-- Bitte wählen --</option>
                        <?php foreach ($ligen as $liga): ?>
                            <option value="<?= htmlspecialchars($liga[0]) ?>" <?= ($_POST['liga_id'] ?? '') == $liga[0] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($liga[2]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;"><i
                    class="fa-solid fa-search"></i> Vereine suchen</button>
        </form>
    </div>

    <!-- SCHRITT 2: Vereins-Auswahl -->
    <?php if ($step >= 2): ?>
        <div class="content-tile" style="max-width: 800px;">
            <?php if ($error && $step >= 2): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div
                    style="color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
                    <?= htmlspecialchars($success) ?>
                </div><?php endif; ?>

            <h3>2. Verein & Schnitt-Art auswählen</h3>
            <form method="POST">
                <input type="hidden" name="saison_id" value="<?= htmlspecialchars($saison_id) ?>">
                <input type="hidden" name="liga_id" value="<?= htmlspecialchars($_POST['liga_id'] ?? '') ?>">

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 2;">
                        <label>Gegnerische Mannschaft:</label>
                        <select name="team_name" class="form-control" required>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= htmlspecialchars($team) ?>" <?= $selected_team === $team ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Schnitt:</label>
                        <select name="schnitt_typ" class="form-control" required>
                            <option value="0" <?= $schnitt_typ == '0' ? 'selected' : '' ?>>Gesamtschnitt</option>
                            <option value="1" <?= $schnitt_typ == '1' ? 'selected' : '' ?>>Heimschnitt</option>
                            <option value="2" <?= $schnitt_typ == '2' ? 'selected' : '' ?>>Auswärtsschnitt</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary"
                    style="width: 100%; padding: 12px; font-size: 1.1rem; background-color: var(--sidebar-color); color: #fff;"><i
                        class="fa-solid fa-play"></i> Spieler auswerten</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 3 && !empty($players)): ?>
        <div class="content-tile" style="overflow-x: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Spieler-Schnitte: <?= htmlspecialchars($selected_team) ?></h3>
                <button type="button" onclick="window.print();" class="btn btn-secondary btn-sm"
                    style="padding: 5px 10px;"><i class="fa-solid fa-print"></i> Drucken</button>
            </div>
            <p style="margin-bottom: 15px; color: #666;">
                Zeigt den
                <strong><?= $schnitt_typ == '1' ? 'Heimschnitt' : ($schnitt_typ == '2' ? 'Auswärtsschnitt' : 'Gesamtschnitt') ?></strong>
                aller in der ausgewählten Liga eingesetzten Spieler an.
            </p>

            <style>
                @media print {

                    nav,
                    header,
                    footer,
                    .action-bar,
                    form,
                    h2 {
                        display: none !important;
                    }

                    body {
                        grid-template-columns: 1fr !important;
                        overflow: visible !important;
                        height: auto !important;
                        margin: 0 !important;
                    }

                    main {
                        grid-column: 1 / -1 !important;
                        overflow: visible !important;
                        padding: 0 !important;
                    }

                    .content-tile {
                        box-shadow: none !important;
                        border: none !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    .content-tile:not(:last-child) {
                        display: none !important;
                    }

                    .btn {
                        display: none !important;
                    }
                }
            </style>

            <table class="admin-table" style="font-size: 0.9rem;">
                <tr>
                    <th style="width: 40px; text-align: center;">Nr.</th>
                    <th style="width: 60px; text-align: center;">Platz</th>
                    <th>Spieler</th>
                    <th style="text-align: center;">Klasse</th>
                    <th style="text-align: center;">Spiele</th>
                    <th style="text-align: center;">Ø Holz</th>
                </tr>
                <?php $counter = 1;
                foreach ($players as $p):
                    // Medaillen-Farben für die Top 3 basierend auf dem Rang
                    $medalColor = '#999';
                    $medalIcon = '';
                    if ($p['rank'] === 1) {
                        $medalColor = '#f1c40f';
                        $medalIcon = ' <i class="fa-solid fa-medal" style="color: #f1c40f;" title="1. Platz"></i>';
                    } elseif ($p['rank'] === 2) {
                        $medalColor = '#bdc3c7';
                        $medalIcon = ' <i class="fa-solid fa-medal" style="color: #bdc3c7;" title="2. Platz"></i>';
                    } elseif ($p['rank'] === 3) {
                        $medalColor = '#cd7f32';
                        $medalIcon = ' <i class="fa-solid fa-medal" style="color: #cd7f32;" title="3. Platz"></i>';
                    }
                    ?>
                    <tr>
                        <td style="text-align: center; color: #aaa;"><?= $counter++ ?></td>
                        <td
                            style="text-align: center; color: <?= $medalColor ?>; font-weight: bold; font-size: <?= $p['rank'] <= 3 ? '1.2rem' : '1rem' ?>;">
                            <?= $p['rank'] ?>.
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong><?= $medalIcon ?>
                        </td>
                        <td style="text-align: center; color: #666; font-size: 0.85rem;">
                            <?php if (!empty($p['mannschaft'])): ?>
                                <strong><?= htmlspecialchars($p['mannschaft']) ?></strong><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($p['altersklasse']) ?>
                        </td>
                        <td style="text-align: center;"><?= $p['spiele'] ?></td>
                        <td
                            style="text-align: center; font-weight: bold; color: <?= $schnitt_typ == '1' ? '#e67e22' : ($schnitt_typ == '2' ? '#3498db' : '#27ae60') ?>;">
                            <?= number_format($p['schnitt'], 2, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php elseif ($step === 3 && empty($players)): ?>
        <div class="content-tile alert-error" style="text-align: center;">
            Für diesen Verein konnten im ausgewählten Modus (Heim/Auswärts) keine Spieler mit Einsätzen gefunden werden.
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>