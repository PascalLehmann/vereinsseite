<?php
session_start();

// Funktion zum Prüfen, ob der User eingeloggt ist
function checkLogin() {
    if (!isset($_SESSION['eingeloggt']) || $_SESSION['eingeloggt'] !== true) {
        header("Location: login.php");
        exit;
    }
}
?>