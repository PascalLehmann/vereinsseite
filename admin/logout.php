<?php
session_start();
session_destroy();

// Da wir uns im Ordner 'admin' befinden, leiten wir zur login.php im selben Ordner um
header("Location: login.php");
exit;
?>