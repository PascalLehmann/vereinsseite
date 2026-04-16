<?php
session_start();
if (empty($_SESSION['permissions']['admin'])) {
    die("Zugriff verweigert: Nur für Admins.");
}

require_once __DIR__ . '/../../../db.php';

$flash_error = $_SESSION['flash_error'] ?? null;
if ($flash_error) {
    unset($_SESSION['flash_error']);
}

$positionen = $pdo->query("SELECT * FROM vorstand_positionen ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_positions = count($positionen);

require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="../dashboard.php" class="btn btn-secondary">&larr; Zurück zum Dashboard</a>
        <a href="erstellen.php" class="btn btn-primary">+ Neue Position anlegen</a>
    </div>

    <h2>Vorstandspositionen verwalten</h2>
    <p style="margin-bottom: 25px; color: #666;">Hier kannst du die Positionen für den Vorstand anlegen und ihre
        Reihenfolge für die Webseite festlegen.</p>

    <?php if ($flash_error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($flash_error) ?>
        </div>
    <?php endif; ?>

    <div class="content-tile">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reihenfolge</th>
                    <th>Positionsname</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_positions > 0): ?>
                    <?php foreach ($positionen as $index => $pos): ?>
                        <tr>
                            <td style="width: 120px; text-align: center;">
                                <?php if ($index > 0): ?>
                                    <a href="reihenfolge.php?id=<?= $pos['id'] ?>&dir=up" class="action-link" title="Nach oben"><i
                                            class="fas fa-arrow-up"></i></a>
                                <?php endif; ?>
                                <?php if ($index < $total_positions - 1): ?>
                                    <a href="reihenfolge.php?id=<?= $pos['id'] ?>&dir=down" class="action-link"
                                        title="Nach unten"><i class="fas fa-arrow-down"></i></a>
                                <?php endif; ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($pos['name']) ?>
                                </strong></td>
                            <td style="width: 150px;">
                                <a href="bearbeiten.php?id=<?= $pos['id'] ?>" class="action-link" title="Bearbeiten"><i
                                        class="fas fa-edit"></i></a>
                                <a href="loeschen.php?id=<?= $pos['id'] ?>" class="delete-link" title="Löschen"
                                    onclick="return confirm('Position \'<?= htmlspecialchars($pos['name']) ?>\' wirklich löschen?');"><i
                                        class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">Noch keine Positionen angelegt.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>