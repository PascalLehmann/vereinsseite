<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$pageTitle = "Admin Login";
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>Admin Login</h1>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message">Login fehlgeschlagen.</p>
            <?php endif; ?>

            <form action="/pages/admin/auth.php" method="POST">
                <label>Benutzername</label>
                <input type="text" name="username" required>

                <label>Passwort</label>
                <input type="password" name="password" required>

                <button type="submit" class="btn-primary">Login</button>
            </form>
        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>