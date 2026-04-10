<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM gegner WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: index.php?deleted=1");
exit;