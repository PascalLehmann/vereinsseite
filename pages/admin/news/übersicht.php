<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$pageTitle = "News Übersicht";
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>News Übersicht</h1>

            <a href="/pages/admin/news/erstellen.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Neue News erstellen
            </a>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titel</th>
                        <th>Datum</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM news ORDER BY datum DESC");
                    while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['titel']); ?></td>
                            <td><?= date("d.m.Y", strtotime($row['datum'])); ?></td>
                            <td>
                                <a href="/pages/admin/news/bearbeiten.php?id=<?= $row['id']; ?>"
                                    class="btn-small">Bearbeiten</a>
                                <a href="/pages/admin/news/loeschen.php?id=<?= $row['id']; ?>"
                                    class="btn-small btn-danger">Löschen</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>