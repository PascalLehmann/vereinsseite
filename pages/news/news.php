<?php
session_start();
// Fehlerberichterstattung für die Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN (2 Ebenen nach oben ins Hauptverzeichnis)
require_once __DIR__ . '/../../db.php';

// 2. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main>
    <h2>Aktuelle News</h2>

    <div class="news-list">
        <?php
        try {
            // News abfragen (neueste zuerst)
            $sql = "SELECT n.id, n.titel, n.inhalt, n.erstellt_am, u.username as autor_name 
                    FROM news n 
                    LEFT JOIN users u ON n.autor_id = u.id 
                    WHERE n.is_deleted = 0 
                    ORDER BY n.erstellt_am DESC";
            $stmt = $pdo->query($sql);
            $news_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Prepared Statement für das Thumbnail (LIMIT 1 für optimale Performance)
            $sqlBilder = "SELECT bild_pfad FROM news_bilder WHERE news_id = :news_id ORDER BY id ASC LIMIT 1";
            $stmtBilder = $pdo->prepare($sqlBilder);

            if (count($news_entries) > 0) {
                foreach ($news_entries as $news) {

                    // START: Die 3D Kachel
                    echo "<article class='content-tile'>";
                    echo "<div class='news-preview'>";

                    // 1. Thumbnail abfragen und rendern
                    $stmtBilder->execute([':news_id' => $news['id']]);
                    $erstes_bild = $stmtBilder->fetchColumn(); // Holt direkt den String, da wir nur 1 Spalte & 1 Zeile abfragen
        
                    echo "<div class='news-thumb'>";
                    if ($erstes_bild) {
                        // Wenn ein Bild existiert
                        echo "<img src='" . htmlspecialchars($erstes_bild) . "' alt='News Thumbnail'>";
                    } else {
                        // Fallback: FontAwesome Icon, wenn kein Bild hochgeladen wurde
                        echo "<i class='fas fa-newspaper'></i>";
                    }
                    echo "</div>";

                    // 2. Textbereich (Titel, Excerpt, Button)
                    echo "<div class='news-preview-text'>";
                    echo "<h3>" . htmlspecialchars($news['titel']) . "</h3>";

                    // Autor und Datum
                    $autor = !empty($news['autor_name']) ? htmlspecialchars($news['autor_name']) : 'Unbekannt';
                    $datum = date('d.m.Y', strtotime($news['erstellt_am']));
                    echo "<small style='color: #6b7280; display: block; margin-bottom: 10px;'><i class='fas fa-user'></i> " . $autor . " &nbsp;|&nbsp; <i class='fas fa-calendar-alt'></i> " . $datum . "</small>";

                    // HTML vom CKEditor strippen (entfernen) und Zeilenumbrüche löschen
                    $clean_text = strip_tags($news['inhalt']);
                    $clean_text = str_replace(["\r", "\n"], ' ', $clean_text);

                    // Ausgabe des gekürzten Textes (CSS white-space: nowrap übernimmt das "... " am Ende)
                    echo "<div class='news-excerpt'>" . htmlspecialchars($clean_text) . "</div>";

                    // Der "Mehr lesen" Button
                    echo "<a href='news-details.php?id=" . $news['id'] . "' class='btn btn-secondary btn-sm'>Mehr lesen</a>";

                    echo "</div>"; // Ende .news-preview-text
                    echo "</div>"; // Ende .news-preview
                    echo "</article>"; // Ende .content-tile
                }
            } else {
                echo "<div class='content-tile'><p>Bisher gibt es noch keine Neuigkeiten.</p></div>";
            }

        } catch (PDOException $e) {
            echo "<div class='content-tile alert-error'>Fehler beim Laden der News: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
</main>

<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>