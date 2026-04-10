<?php
session_start();

function checkLogin() {
    // Wenn NICHT eingeloggt, schicke zum Login
    if (!isset($_SESSION['eingeloggt']) || $_SESSION['eingeloggt'] !== true) {
        // Da auth.php und login.php im gleichen Ordner (admin) liegen:
        header("Location: login.php");
        exit;
    }
}
?>