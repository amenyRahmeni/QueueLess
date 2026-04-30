<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Dashboard';
$pageDescription = 'Tableau de bord utilisateur QueueLess.';
$currentPage = 'dashboard';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$stats = [
    'total' => 0,
    'upcoming' => 0,
    'cancelled' => 0,
];
$nextReservations = [];
$recentReservations = [];
$databaseWarning = null;

try {
    $pdo = getPDO();

    $statsStatement = $pdo->prepare(
        'SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN r.statut = "reservee" AND sl.date_slot >= CURDATE() THEN 1 ELSE 0 END), 0) AS upcoming,
            COALESCE(SUM(CASE WHEN r.statut = "annulee" THEN 1 ELSE 0 END), 0) AS cancelled
        FROM reservations r
        INNER JOIN slots sl ON sl.id = r.slot_id
        WHERE r.user_id = :user_id'
    );
    $statsStatement->execute([
        'user_id' => $userId,
    ]);

    $statsRow = $statsStatement->fetch();

    if ($statsRow) {
        $stats = [
            'total' => (int) $statsRow['total'],
            'upcoming' => (int) $statsRow['upcoming'],
            'cancelled' => (int) $statsRow['cancelled'],
        ];
    }

    $nextStatement = $pdo->prepare(
        'SELECT
            r.statut,
            s.nom AS service_nom,
            s.categorie,
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin
        FROM reservations r
        INNER JOIN services s ON s.id = r.service_id
        INNER JOIN slots sl ON sl.id = r.slot_id
        WHERE r.user_id = :user_id
            AND r.statut = "reservee"
            AND sl.date_slot >= CURDATE()
        ORDER BY sl.date_slot ASC, sl.heure_debut ASC
        LIMIT 3'
    );
    $nextStatement->execute([
        'user_id' => $userId,
    ]);
    $nextReservations = $nextStatement->fetchAll();

    $recentStatement = $pdo->prepare(
        'SELECT
            r.statut,
            s.nom AS service_nom,
            sl.date_slot,
            sl.heure_debut
        FROM reservations r
        INNER JOIN services s ON s.id = r.service_id
        INNER JOIN slots sl ON sl.id = r.slot_id
        WHERE r.user_id = :user_id
        ORDER BY sl.date_slot DESC, sl.heure_debut DESC
        LIMIT 5'
    );
    $recentStatement->execute([
        'user_id' => $userId,
    ]);
    $recentReservations = $recentStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'La base de donnees n est pas encore disponible. Importez le fichier SQL pour voir les vraies statistiques.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_user.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Bonjour <?= e((string) ($user['prenom'] ?? '')); ?></span>
                <h1>Bienvenue dans votre espace QueueLess</h1>
                <p>Retrouvez ici vos reservations a venir, votre activite recente et un acces rapide aux services.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <article class="stat-panel">
                    <span>Total reservations</span>
                    <strong><?= e((string) $stats['total']); ?></strong>
                </article>

                <article class="stat-panel">
                    <span>Reservations a venir</span>
                    <strong><?= e((string) $stats['upcoming']); ?></strong>
                </article>

                <article class="stat-panel">
                    <span>Reservations annulees</span>
                    <strong><?= e((string) $stats['cancelled']); ?></strong>
                </article>
            </div>

            <div class="dashboard-columns">
                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Prochaines reservations</span>
                            <h2>Vos prochains rendez-vous</h2>
                        </div>
                        <a href="<?= url('pages/my_reservations.php'); ?>">Voir tout</a>
                    </div>

                    <?php if ($nextReservations): ?>
                        <div class="reservation-list">
                            <?php foreach ($nextReservations as $reservation): ?>
                                <article class="reservation-item">
                                    <div>
                                        <h3><?= e((string) $reservation['service_nom']); ?></h3>
                                        <p><?= e((string) $reservation['categorie']); ?></p>
                                    </div>
                                    <div class="reservation-meta">
                                        <span><?= e(format_datetime((string) $reservation['date_slot'], 'd/m/Y')); ?></span>
                                        <span><?= e(substr((string) $reservation['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $reservation['heure_fin'], 0, 5)); ?></span>
                                        <span class="<?= e(badge_class_for((string) $reservation['statut'])); ?>"><?= e(reservation_status_label((string) $reservation['statut'])); ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucune reservation a venir pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Historique rapide</span>
                            <h2>Derniere activite</h2>
                        </div>
                    </div>

                    <?php if ($recentReservations): ?>
                        <div class="activity-list">
                            <?php foreach ($recentReservations as $reservation): ?>
                                <article class="activity-item">
                                    <div>
                                        <strong><?= e((string) $reservation['service_nom']); ?></strong>
                                        <p><?= e(format_datetime((string) $reservation['date_slot'], 'd/m/Y')); ?> a <?= e(substr((string) $reservation['heure_debut'], 0, 5)); ?></p>
                                    </div>
                                    <span class="<?= e(badge_class_for((string) $reservation['statut'])); ?>"><?= e(reservation_status_label((string) $reservation['statut'])); ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Votre historique apparaitra ici apres vos premieres reservations.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

