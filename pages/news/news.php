<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN (Das ist der Fix für den Fehler!)
// Wir gehen von pages/news/ zwei Ordner hoch ins Hauptverzeichnis zur db.php
require_once __DIR__ . '/../../db.php';

// 2. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main>
    <h2>Aktuelle News</h2>

    <?php
    try {
        // 1. Alle News abfragen (Jetzt mit den korrekten deutschen Spaltennamen!)
        $sql = "SELECT id, titel, inhalt, erstellt_am FROM news ORDER BY erstellt_am DESC";
        $stmt = $pdo->query($sql);
        $news_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Das Prepared Statement für die Bilder vorbereiten (Optimierung!)
        // Das ist wie das Vorkompilieren einer Funktion. Wir rufen sie später im Loop nur noch auf.
        $sqlBilder = "SELECT bild_pfad FROM news_bilder WHERE news_id = :news_id";
        $stmtBilder = $pdo->prepare($sqlBilder);

        if (count($news_entries) > 0) {
            foreach ($news_entries as $news) {
                echo "<article class='news-item' style='margin-bottom: 40px;'>";
                echo "<h3>" . htmlspecialchars($news['titel']) . "</h3>";
                echo "<small style='color: #666;'>Veröffentlicht am: " . date('d.m.Y H:i', strtotime($news['erstellt_am'])) . "</small>";
                echo "<p style='margin-top: 10px;'>" . nl2br(htmlspecialchars($news['inhalt'])) . "</p>";

                // 3. Bilder für exakt diese News-ID abfragen
                $stmtBilder->execute([':news_id' => $news['id']]);
                // FETCH_COLUMN holt direkt ein flaches Array der Pfade (z.B. ['/uploads/a.jpg', '/uploads/b.png'])
                $bilder = $stmtBilder->fetchAll(PDO::FETCH_COLUMN);

                // 4. Wenn Bilder existieren, als kleine Galerie ausgeben
                if (count($bilder) > 0) {
                    echo "<div class='news-gallery' style='display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;'>";
                    foreach ($bilder as $pfad) {
                        // WICHTIG: absolute Pfade nutzen!
                        echo "<img src='" . htmlspecialchars($pfad) . "' alt='News Bild' style='max-width: 200px; border-radius: 8px; object-fit: cover;'>";
                    }
                    echo "</div>";
                }

                echo "</article><hr>";
            }
        } else {
            echo "<p>Bisher gibt es noch keine Neuigkeiten.</p>";
        }

    } catch (PDOException $e) {
        echo "<p style='color: red;'>Fehler beim Laden der News: " . $e->getMessage() . "</p>";
    }
    ?>
</main>

<?php
require_once __DIR__ . '/../../templates/footer.php';
?>