<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: /pages/admin/login.php");
    exit;
}


$pageTitle = "Admin Dashboard";

include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>Admin Dashboard</h1>

            <div class="admin-grid">

                <a class="admin-card" href="/pages/admin/news/übersicht.php">
                    <i class="fa-solid fa-newspaper"></i>
                    <h3>News verwalten</h3>
                </a>

                <a class="admin-card" href="/pages/admin/mitglieder/übersicht.php">
                    <i class="fa-solid fa-users"></i>
                    <h3>Mitglieder verwalten</h3>
                </a>

                <a class="admin-card" href="/pages/admin/gegner/übersicht.php">
                    <i class="fa-solid fa-shield"></i>
                    <h3>Gegner verwalten</h3>
                </a>

                <a class="admin-card" href="/pages/admin/termine/übersicht.php">
                    <i class="fa-solid fa-calendar-days"></i>
                    <h3>Termine verwalten</h3>
                </a>

            </div>

        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>