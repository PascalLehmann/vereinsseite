<?php
// 1. Session starten (Der "Shared Memory" für unseren eingeloggten Zustand)
session_start();

// 2. Sicherheits-Check: Wurde das Skript wirklich über das Login-Formular aufgerufen?
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Falls jemand die URL direkt eingibt -> Zurück zum Login
    header("Location: login.php");
    exit;
}

// 3. Datenbankverbindung laden (Pfad geht 2 Ebenen hoch ins Root)
require_once __DIR__ . '/../../db.php';

// 4. Eingaben holen und säubern
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// --- SCHRITT A: User finden und Passwort prüfen ---

// Prepared Statement: Trennt den SQL-Code strikt von den User-Daten (Schutz vor SQL-Injection)
$sqlUser = "SELECT id, username, password_hash FROM users WHERE username = :username";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->execute([':username' => $username]);

$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// password_verify() macht die ganze Kryptographie-Arbeit (Salt & Hash abgleichen)
if ($user && password_verify($password, $user['password_hash'])) {

    // --- SCHRITT B: Rollen des Users laden ---

    // Wir joinen die Kreuztabelle mit der Rollen-Tabelle
    $sqlRoles = "
        SELECT r.name 
        FROM roles r 
        JOIN user_roles ur ON r.id = ur.role_id 
        WHERE ur.user_id = :user_id
    ";
    $stmtRoles = $pdo->prepare($sqlRoles);
    $stmtRoles->execute([':user_id' => $user['id']]);

    // PDO::FETCH_COLUMN holt nur die erste Spalte (r.name) und macht daraus ein flaches Array
    // Ergebnis sieht dann so aus: array('admin', 'autor')
    $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

    // --- SCHRITT C: Session befüllen (Login erfolgreich) ---

    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['roles'] = $roles; // Hier liegt jetzt das Array mit allen Rechten!

    // Weiterleitung ins Kontrollzentrum
    header("Location: dashboard.php");
    exit;

} else {
    // Login fehlgeschlagen (Falscher User oder falsches Passwort)
    // Wir leiten zurück und hängen einen Fehler-Code an die URL an
    header("Location: login.php?error=1");
    exit;
}
?>