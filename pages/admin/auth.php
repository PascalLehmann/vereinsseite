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
        'news_create' => false,
        'news_edit' => false,
        'news_delete' => false,
        'news_delete_hard' => false,
        'termine_create' => false,
        'termine_edit' => false,
        'termine_delete' => false,
        'termine_delete_hard' => false,
        'mitglieder_create' => false,
        'mitglieder_edit' => false,
        'mitglieder_delete' => false,
        'mitglieder_bestleistungen' => false,
        'galerie_upload' => false,
        'galerie_delete' => false,
        'galerie_delete_hard' => false,
        'galerie_kat_create' => false,
        'galerie_kat_delete' => false,
        'galerie_kat_delete_hard' => false,
        'admin' => false
    ];

    try {
        // Wir joinen die Kreuztabelle mit der Rollen-Tabelle
        $sqlRoles = "
            SELECT r.name, r.perm_admin,
                   r.perm_news_create, r.perm_news_edit, r.perm_news_delete, r.perm_news_delete_hard,
                   r.perm_termine_create, r.perm_termine_edit, r.perm_termine_delete, r.perm_termine_delete_hard,
                   r.perm_mitglieder_create, r.perm_mitglieder_edit, r.perm_mitglieder_delete, r.perm_mitglieder_bestleistungen, r.perm_galerie_upload, r.perm_galerie_delete, r.perm_galerie_delete_hard,
                   r.perm_galerie_kat_create, r.perm_galerie_kat_delete, r.perm_galerie_kat_delete_hard
            FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = :user_id
        ";
        $stmtRoles = $pdo->prepare($sqlRoles);
        $stmtRoles->execute([':user_id' => $user['id']]);
        $rolesData = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rolesData as $r) {
            $roles[] = $r['name'];

            if (!empty($r['perm_news_create']))
                $permissions['news_create'] = true;
            if (!empty($r['perm_news_edit']))
                $permissions['news_edit'] = true;
            if (!empty($r['perm_news_delete']))
                $permissions['news_delete'] = true;
            if (!empty($r['perm_news_delete_hard']))
                $permissions['news_delete_hard'] = true;
            if (!empty($r['perm_termine_create']))
                $permissions['termine_create'] = true;
            if (!empty($r['perm_termine_edit']))
                $permissions['termine_edit'] = true;
            if (!empty($r['perm_termine_delete']))
                $permissions['termine_delete'] = true;
            if (!empty($r['perm_termine_delete_hard']))
                $permissions['termine_delete_hard'] = true;
            if (!empty($r['perm_mitglieder_create']))
                $permissions['mitglieder_create'] = true;
            if (!empty($r['perm_mitglieder_edit']))
                $permissions['mitglieder_edit'] = true;
            if (!empty($r['perm_mitglieder_delete']))
                $permissions['mitglieder_delete'] = true;
            if (!empty($r['perm_mitglieder_bestleistungen']))
                $permissions['mitglieder_bestleistungen'] = true;
            if (!empty($r['perm_galerie_upload']))
                $permissions['galerie_upload'] = true;
            if (!empty($r['perm_galerie_delete']))
                $permissions['galerie_delete'] = true;
            if (!empty($r['perm_galerie_kat_create']))
                $permissions['galerie_kat_create'] = true;
            if (!empty($r['perm_galerie_kat_delete']))
                $permissions['galerie_kat_delete'] = true;
            if (!empty($r['perm_galerie_delete_hard']))
                $permissions['galerie_delete_hard'] = true;
            if (!empty($r['perm_galerie_kat_delete_hard']))
                $permissions['galerie_kat_delete_hard'] = true;

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