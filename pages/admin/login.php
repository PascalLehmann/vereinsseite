<?php
// 1. Session starten - IMMER als Erstes!
session_start();

// 2. Prüfen: Ist der User schon eingeloggt? (Wie ein Null-Pointer-Check)
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit; // Wichtig: Code-Ausführung hier abbrechen!
}

// Header einbinden (mit absolutem Server-Pfad)
require_once __DIR__ . '/../../templates/header.php';
?>

<main class="login-container">
    <h2>Login</h2>

    <?php
    // Fehlermeldung anzeigen, falls auth.php uns mit einem Fehler zurückschickt
    if (isset($_GET['error'])) {
        echo '<p style="color: red;">Benutzername oder Passwort falsch!</p>';
    }
    ?>

    <form action="auth.php" method="POST">
        <div>
            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Einloggen</button>
    </form>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>