<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Dashboard Admin';
$pageDescription = 'Tableau de bord administrateur QueueLess.';
$currentPage = 'admin_dashboard';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$metrics = [
    'users' => 0,
    'services' => 0,
    'reservations' => 0,
    'available_slots' => 0,
];
$topServices = [];
$recentReservations = [];
$databaseWarning = null;

try {
    $pdo = getPDO();

    $metrics['users'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $metrics['services'] = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $metrics['reservations'] = (int) $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    $metrics['available_slots'] = (int) $pdo->query('SELECT COUNT(*) FROM slots WHERE disponible = 1')->fetchColumn();

    $topServicesStatement = $pdo->query(
        'SELECT s.nom, s.categorie, COUNT(r.id) AS total_reservations
        FROM services s
        LEFT JOIN reservations r ON r.service_id = s.id AND r.statut = "reservee"
        GROUP BY s.id, s.nom, s.categorie
        ORDER BY total_reservations DESC, s.nom ASC
        LIMIT 5'
    );
    $topServices = $topServicesStatement->fetchAll();

    $recentReservationsStatement = $pdo->query(
        'SELECT
            r.id,
            r.statut,
            u.prenom,
            u.nom,
            s.nom AS service_nom,
            sl.date_slot,
            sl.heure_debut
        FROM reservations r
        INNER JOIN users u ON u.id = r.user_id
        INNER JOIN services s ON s.id = r.service_id
        INNER JOIN slots sl ON sl.id = r.slot_id
        ORDER BY r.date_reservation DESC
        LIMIT 6'
    );
    $recentReservations = $recentReservationsStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'La base de donnees n est pas encore disponible. Importez le script SQL pour activer le dashboard admin.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Administration QueueLess</span>
                <h1>Pilotez l activite de la plateforme</h1>
                <p>Consultez les indicateurs cles, suivez les reservations recentes et gardez une vue globale sur les services.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <div class="stats-grid admin-stats-grid">
                <article class="stat-panel">
                    <span>Utilisateurs</span>
                    <strong><?= e((string) $metrics['users']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Services</span>
                    <strong><?= e((string) $metrics['services']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Reservations</span>
                    <strong><?= e((string) $metrics['reservations']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Creneaux disponibles</span>
                    <strong><?= e((string) $metrics['available_slots']); ?></strong>
                </article>
            </div>

            <div class="dashboard-columns">
                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Top services</span>
                            <h2>Les plus reserves</h2>
                        </div>
                        <a href="<?= url('admin/stats.php'); ?>">Voir les statistiques</a>
                    </div>

                    <?php if ($topServices): ?>
                        <div class="activity-list">
                            <?php foreach ($topServices as $service): ?>
                                <article class="activity-item">
                                    <div>
                                        <strong><?= e((string) $service['nom']); ?></strong>
                                        <p><?= e((string) $service['categorie']); ?></p>
                                    </div>
                                    <span class="status-badge success"><?= e((string) $service['total_reservations']); ?> reservation(s)</span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucune donnee de service disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Activite recente</span>
                            <h2>Dernieres reservations</h2>
                        </div>
                        <a href="<?= url('admin/reservations.php'); ?>">Voir tout</a>
                    </div>

                    <?php if ($recentReservations): ?>
                        <div class="activity-list">
                            <?php foreach ($recentReservations as $reservation): ?>
                                <article class="activity-item">
                                    <div>
                                        <strong><?= e(trim((string) (($reservation['prenom'] ?? '') . ' ' . ($reservation['nom'] ?? '')))); ?></strong>
                                        <p><?= e((string) $reservation['service_nom']); ?> - <?= e(format_datetime((string) $reservation['date_slot'], 'd/m/Y')); ?> a <?= e(substr((string) $reservation['heure_debut'], 0, 5)); ?></p>
                                    </div>
                                    <span class="<?= e(badge_class_for((string) $reservation['statut'])); ?>"><?= e(reservation_status_label((string) $reservation['statut'])); ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucune reservation recente a afficher.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

