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

<main style="display: flex; justify-content: center; align-items: center; min-height: 60vh; padding: 20px;">
    <div class="content-tile" style="max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fas fa-user-shield" style="font-size: 3rem; color: var(--sidebar-color); margin-bottom: 15px;"></i>
            <h2 style="margin-bottom: 0;">Admin Login</h2>
        </div>

        <?php
        // Fehlermeldung anzeigen, falls auth.php uns mit einem Fehler zurückschickt
        if (isset($_GET['error'])) {
            echo '<div class="alert-error" style="text-align: center;">Benutzername oder Passwort falsch!</div>';
        }
        ?>

        <form action="auth.php" method="POST">
            <div class="form-group">
                <label for="username">Benutzername:</label>
                <input type="text" id="username" name="username" class="form-control" required autocomplete="username">
            </div>
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;">
                <i class="fas fa-sign-in-alt"></i> Einloggen
            </button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>