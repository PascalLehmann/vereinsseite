<?php
session_start();

// Alle Session-Variablen löschen
$_SESSION = array();

// Falls Cookies für die Session genutzt werden, diese auch löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session endgültig zerstören
session_destroy();

// Umleitung zur Hauptseite (eine Ebene höher aus dem admin-Ordner raus)
header("Location: ../index.php");
exit;
?>