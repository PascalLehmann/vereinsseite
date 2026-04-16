<?php
session_start();
if (empty($_SESSION['permissions']['admin'])) {
    die("Zugriff verweigert.");
}
require_once __DIR__ . '/../../../db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$dir = $_GET['dir'] ?? '';

if (!$id || !in_array($dir, ['up', 'down'])) {
    header("Location: uebersicht.php");
    exit;
}

$pdo->beginTransaction();

try {
    // Aktuelle Sortiernummer des zu bewegenden Elements holen
    $stmt_current = $pdo->prepare("SELECT sort_order FROM vorstand_positionen WHERE id = ?");
    $stmt_current->execute([$id]);
    $current_order = $stmt_current->fetchColumn();

    if ($dir === 'up') {
        // Element darüber finden (nächstkleinere Sortiernummer)
        $stmt_other = $pdo->prepare("SELECT id, sort_order FROM vorstand_positionen WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1");
        $stmt_other->execute([$current_order]);
        $other = $stmt_other->fetch(PDO::FETCH_ASSOC);
    } else { // 'down'
        // Element darunter finden (nächstgrößere Sortiernummer)
        $stmt_other = $pdo->prepare("SELECT id, sort_order FROM vorstand_positionen WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1");
        $stmt_other->execute([$current_order]);
        $other = $stmt_other->fetch(PDO::FETCH_ASSOC);
    }

    if ($other) {
        // Sortiernummern tauschen
        $stmt_swap = $pdo->prepare("UPDATE vorstand_positionen SET sort_order = ? WHERE id = ?");
        $stmt_swap->execute([$other['sort_order'], $id]);
        $stmt_swap->execute([$current_order, $other['id']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Fehler beim Ändern der Reihenfolge: " . $e->getMessage());
}

header("Location: uebersicht.php");
exit;