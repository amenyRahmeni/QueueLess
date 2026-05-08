<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/owner_check.php';

$pageTitle = 'Reservations';
$pageDescription = 'Reservations des services du proprietaire.';
$currentPage = 'owner_reservations';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$ownerId = (int) (current_user()['id'] ?? 0);
$reservations = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'SELECT
            r.id,
            r.statut,
            r.commentaire,
            r.date_reservation,
            r.slot_id,
            u.prenom,
            u.nom,
            s.nom AS service_nom,
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin
        FROM reservations r
        INNER JOIN users u ON u.id = r.user_id
        INNER JOIN services s ON s.id = r.service_id
        INNER JOIN slots sl ON sl.id = r.slot_id
        WHERE s.owner_id = :owner_id
        ORDER BY r.date_reservation DESC'
    );
    $statement->execute([
        'owner_id' => $ownerId,
    ]);
    $reservations = $statement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger vos reservations pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Reservations</span>
                <h1>Liste complete</h1>
                <p>Consultez les demandes de vos services et mettez a jour leur statut.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Reservations</span>
                        <h2>Demandes recues</h2>
                    </div>
                </div>

                <?php if ($reservations): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Statut</th>
                                    <th>Commentaire</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td><?= e(trim((string) (($reservation['prenom'] ?? '') . ' ' . ($reservation['nom'] ?? '')))); ?></td>
                                        <td><?= e((string) $reservation['service_nom']); ?></td>
                                        <td><?= e(format_datetime((string) $reservation['date_slot'], 'd/m/Y')); ?></td>
                                        <td><?= e(substr((string) $reservation['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $reservation['heure_fin'], 0, 5)); ?></td>
                                        <td><span class="<?= e(badge_class_for((string) $reservation['statut'])); ?>"><?= e(reservation_status_label((string) $reservation['statut'])); ?></span></td>
                                        <td><?= e((string) ($reservation['commentaire'] ?: '-')); ?></td>
                                        <td>
                                            <form class="table-inline-form" method="POST" action="<?= url('actions/update_reservation_status.php'); ?>">
                                                <input type="hidden" name="reservation_id" value="<?= e((string) $reservation['id']); ?>">
                                                <select name="statut">
                                                    <option value="reservee" <?= (string) $reservation['statut'] === 'reservee' ? 'selected' : ''; ?>>Acceptee</option>
                                                    <option value="annulee" <?= (string) $reservation['statut'] === 'annulee' ? 'selected' : ''; ?>>Annulee</option>
                                                    <option value="terminee" <?= (string) $reservation['statut'] === 'terminee' ? 'selected' : ''; ?>>Terminee</option>
                                                </select>
                                                <button class="table-button" type="submit">Mettre a jour</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>Aucune reservation disponible pour vos services.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
