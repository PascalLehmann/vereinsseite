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

    $roles = [];
    $permissions = [
        'news' => false,
        'termine' => false,
        'bestleistungen' => false,
        'admin' => false
    ];

    try {
        // Wir joinen die Kreuztabelle mit der Rollen-Tabelle
        $sqlRoles = "
            SELECT r.name, r.perm_news, r.perm_termine, r.perm_bestleistungen, r.perm_admin 
            FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = :user_id
        ";
        $stmtRoles = $pdo->prepare($sqlRoles);
        $stmtRoles->execute([':user_id' => $user['id']]);
        $rolesData = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rolesData as $r) {
            $roles[] = $r['name'];
            if (!empty($r['perm_news']))
                $permissions['news'] = true;
            if (!empty($r['perm_termine']))
                $permissions['termine'] = true;
            if (!empty($r['perm_bestleistungen']))
                $permissions['bestleistungen'] = true;
            if (!empty($r['perm_admin']))
                $permissions['admin'] = true;
        }
    } catch (PDOException $e) {
        // Fängt den Fehler ab, falls Spalten in der Datenbank fehlen!
        die("<div style='color:red; padding:20px; font-family:sans-serif;'><h3>Datenbank-Fehler beim Login!</h3><p>Du hast sehr wahrscheinlich vergessen, die neuen Berechtigungs-Spalten in phpMyAdmin anzulegen.</p><p><b>Systemmeldung:</b> " . $e->getMessage() . "</p></div>");
    }

    // --- SCHRITT C: Session befüllen (Login erfolgreich) ---

    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['roles'] = $roles; // Hier liegt jetzt das Array mit allen Rechten!
    $_SESSION['permissions'] = $permissions; // NEU: Hier liegen die genauen Checkbox-Rechte

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