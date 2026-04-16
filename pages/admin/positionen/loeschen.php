<?php
session_start();
if (empty($_SESSION['permissions']['admin'])) {
    die("Zugriff verweigert.");
}
require_once __DIR__ . '/../../../db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    // Name der zu löschenden Position holen
    $stmt_name = $pdo->prepare("SELECT name FROM vorstand_positionen WHERE id = ?");
    $stmt_name->execute([$id]);
    $position_name = $stmt_name->fetchColumn();

    if ($position_name) {
        // Prüfen, ob die Position noch verwendet wird
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM mitglieder WHERE vorstands_rolle = ?");
        $stmt_check->execute([$position_name]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            $_SESSION['flash_error'] = "Position '" . htmlspecialchars($position_name) . "' kann nicht gelöscht werden, da sie noch von $count Mitglied(ern) verwendet wird. Bitte weise diesen Mitgliedern zuerst eine andere Rolle zu.";
            header("Location: uebersicht.php");
            exit;
        } else {
            // Wenn nicht in Gebrauch, löschen
            $stmt_delete = $pdo->prepare("DELETE FROM vorstand_positionen WHERE id = ?");
            $stmt_delete->execute([$id]);
        }
    }
}

header("Location: uebersicht.php");
exit;