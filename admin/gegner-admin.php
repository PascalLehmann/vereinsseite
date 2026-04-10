<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Gegner verwalten";
include_once '../includes/header.php';

// Gegner löschen Logik
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM gegner WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: gegner-admin.php");
}

$gegner = $pdo->query("SELECT * FROM gegner ORDER BY name ASC")->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Gegner-Verzeichnis</h1>
                <button onclick="document.getElementById('add-gegner-form').style.display='block'" class="read-more">+ Gegner hinzufügen</button>
            </div>

            <div id="add-gegner-form" class="news-card" style="display:none; margin-bottom: 30px; background: #f9f9f9;">
                <form action="gegner-save.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <input type="text" name="name" placeholder="Vereinsname" required style="padding:10px;">
                        <input type="text" name="strasse" placeholder="Straße & Hausnummer" style="padding:10px;">
                        <input type="text" name="plz" placeholder="PLZ" style="padding:10px;">
                        <input type="text" name="ort" placeholder="Ort" style="padding:10px;">
                    </div>
                    <button type="submit" class="read-more" style="margin-top:15px;">Speichern</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Adresse</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gegner as $g): ?>
                    <tr>
    <td><strong><?= htmlspecialchars($g['name']) ?></strong></td>
    <td><?= htmlspecialchars($g['strasse'] . ", " . $g['plz'] . " " . $g['ort']) ?></td>
    <td style="text-align: center;">
        <a href="gegner-edit.php?id=<?= $g['id'] ?>" style="color: var(--secondary-blue); margin-right: 15px;">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
        <a href="gegner-delete.php?id=<?= $g['id'] ?>" 
           onclick="return confirm('Möchtest du diesen Gegner wirklich löschen?')" 
           style="color: #e74c3c;">
            <i class="fa-solid fa-trash"></i>
        </a>
    </td>
</tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</div>