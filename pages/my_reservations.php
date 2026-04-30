<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Mes reservations';
$pageDescription = 'Historique des reservations utilisateur QueueLess.';
$currentPage = 'reservations';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$reservations = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $reservationsStatement = $pdo->prepare(
        'SELECT
            r.id,
            r.statut,
            r.commentaire,
            r.date_reservation,
            r.service_id,
            s.nom AS service_nom,
            s.categorie,
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin,
            a.id AS review_id,
            CASE
                WHEN r.statut = "reservee" AND TIMESTAMP(sl.date_slot, sl.heure_fin) < NOW() THEN "terminee"
                ELSE r.statut
            END AS display_statut,
            CASE
                WHEN r.statut = "reservee" AND TIMESTAMP(sl.date_slot, sl.heure_debut) >= NOW() THEN 1
                ELSE 0
            END AS can_cancel,
            CASE
                WHEN r.statut IN ("reservee", "terminee") AND TIMESTAMP(sl.date_slot, sl.heure_fin) <= NOW() THEN 1
                ELSE 0
            END AS can_review
        FROM reservations r
        INNER JOIN services s ON s.id = r.service_id
        INNER JOIN slots sl ON sl.id = r.slot_id
        LEFT JOIN avis a ON a.user_id = r.user_id AND a.service_id = r.service_id
        WHERE r.user_id = :user_id
        ORDER BY sl.date_slot DESC, sl.heure_debut DESC'
    );
    $reservationsStatement->execute([
        'user_id' => $userId,
    ]);
    $reservations = $reservationsStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'La base de donnees n est pas encore disponible. Importez le script SQL pour afficher l historique complet.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_user.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Historique utilisateur</span>
                <h1>Mes reservations</h1>
                <p>Retrouvez l ensemble de vos rendez-vous, leurs statuts et les details utiles.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Liste complete</span>
                        <h2>Reservations enregistrees</h2>
                    </div>
                </div>

                <?php if ($reservations): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Categorie</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td><?= e((string) $reservation['service_nom']); ?></td>
                                        <td><?= e((string) $reservation['categorie']); ?></td>
                                        <td><?= e(format_datetime((string) $reservation['date_slot'], 'd/m/Y')); ?></td>
                                        <td><?= e(substr((string) $reservation['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $reservation['heure_fin'], 0, 5)); ?></td>
                                        <td>
                                            <span class="<?= e(badge_class_for((string) $reservation['display_statut'])); ?>">
                                                <?= e(reservation_status_label((string) $reservation['display_statut'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="table-link" href="<?= url('pages/service_details.php?id=' . (int) ($reservation['service_id'] ?? 0)); ?>">
                                                    Details
                                                </a>

                                                <?php if ((int) ($reservation['can_cancel'] ?? 0) === 1): ?>
                                                    <form method="POST" action="<?= url('actions/cancel_reservation.php'); ?>">
                                                        <input type="hidden" name="reservation_id" value="<?= e((string) ($reservation['id'] ?? 0)); ?>">
                                                        <button
                                                            class="table-button danger"
                                                            type="submit"
                                                            data-confirm="Voulez-vous vraiment annuler cette reservation ?"
                                                        >
                                                            Annuler
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ((int) ($reservation['can_review'] ?? 0) === 1): ?>
                                                    <a class="table-link" href="<?= url('pages/add_review.php?reservation_id=' . (int) ($reservation['id'] ?? 0)); ?>">
                                                        <?= (int) ($reservation['review_id'] ?? 0) > 0 ? 'Modifier avis' : 'Laisser un avis'; ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>Vous n avez pas encore de reservations enregistrees.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
