<?php
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$id = (int) ($_GET['id'] ?? 0);

// Bilder löschen
$stmt = $pdo->prepare("SELECT bild_pfad FROM news_bilder WHERE news_id = ?");
$stmt->execute([$id]);
$bilder = $stmt->fetchAll();

foreach ($bilder as $bild) {
    $path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/news/' . $bild['bild_pfad'];
    if (file_exists($path))
        unlink($path);
}

$pdo->prepare("DELETE FROM news_bilder WHERE news_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);

header("Location: /pages/admin/news/übersicht.php");
exit;
