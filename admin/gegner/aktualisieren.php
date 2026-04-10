<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE gegner SET name = ?, strasse = ?, plz = ?, ort = ? WHERE id = ?");
    $stmt->execute([$_POST['name'], $_POST['strasse'], $_POST['plz'], $_POST['ort'], $_POST['id']]);
}
header("Location: index.php?updated=1");
exit;