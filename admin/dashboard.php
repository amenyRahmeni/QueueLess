<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Dashboard Admin';
$pageDescription = 'Tableau de bord administrateur QueueLess.';
$currentPage = 'admin_dashboard';
$bodyClass = 'admin-dashboard-page';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$metrics = [
    'users' => 0,
    'services' => 0,
    'active_services' => 0,
    'reservations' => 0,
    'available_slots' => 0,
    'occupied_slots' => 0,
    'reviews' => 0,
];
$reservationStatusCounts = [
    'reservee' => 0,
    'terminee' => 0,
    'annulee' => 0,
];
$topServices = [];
$recentReservations = [];
$nextSlots = [];
$databaseWarning = null;

try {
    $pdo = getPDO();

    $metrics['users'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $metrics['services'] = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $metrics['active_services'] = (int) $pdo->query('SELECT COUNT(*) FROM services WHERE actif = 1')->fetchColumn();
    $metrics['reservations'] = (int) $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    $metrics['available_slots'] = (int) $pdo->query('SELECT COUNT(*) FROM slots WHERE disponible = 1')->fetchColumn();
    $metrics['occupied_slots'] = (int) $pdo->query('SELECT COUNT(*) FROM slots WHERE disponible = 0')->fetchColumn();
    $metrics['reviews'] = (int) $pdo->query('SELECT COUNT(*) FROM avis')->fetchColumn();

    $statusStatement = $pdo->query(
        'SELECT statut, COUNT(*) AS total
        FROM reservations
        GROUP BY statut'
    );
    foreach ($statusStatement->fetchAll() as $statusRow) {
        $statusKey = (string) ($statusRow['statut'] ?? '');
        if (array_key_exists($statusKey, $reservationStatusCounts)) {
            $reservationStatusCounts[$statusKey] = (int) $statusRow['total'];
        }
    }

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

    $nextSlotsStatement = $pdo->query(
        'SELECT
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin,
            sl.disponible,
            s.nom AS service_nom,
            s.categorie
        FROM slots sl
        INNER JOIN services s ON s.id = sl.service_id
        WHERE sl.date_slot >= CURDATE()
        ORDER BY sl.date_slot ASC, sl.heure_debut ASC
        LIMIT 5'
    );
    $nextSlots = $nextSlotsStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'La base de donnees n est pas encore disponible. Importez le script SQL pour activer le dashboard admin.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="admin-dashboard-hero">
                <div>
                    <span class="dashboard-kicker">Dashboard</span>
                    <h1>Vue generale</h1>
                    <p>Suivez les reservations, les creneaux et les services actifs de QueueLess.</p>
                </div>

                <div class="hero-action-row">
                    <a class="btn btn-primary" href="<?= url('admin/add_service.php'); ?>">Ajouter service</a>
                    <a class="btn btn-secondary" href="<?= url('admin/services.php'); ?>">Gerer services</a>
                </div>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <div class="admin-overview-grid">
                <article class="stat-panel stat-panel-primary">
                    <span>Utilisateurs</span>
                    <strong><?= e((string) $metrics['users']); ?></strong>
                    <small>Comptes inscrits</small>
                </article>
                <article class="stat-panel">
                    <span>Services</span>
                    <strong><?= e((string) $metrics['services']); ?></strong>
                    <small><?= e((string) $metrics['active_services']); ?> actifs</small>
                </article>
                <article class="stat-panel">
                    <span>Reservations</span>
                    <strong><?= e((string) $metrics['reservations']); ?></strong>
                    <small><?= e((string) $reservationStatusCounts['reservee']); ?> en cours</small>
                </article>
                <article class="stat-panel">
                    <span>Creneaux disponibles</span>
                    <strong><?= e((string) $metrics['available_slots']); ?></strong>
                    <small><?= e((string) $metrics['occupied_slots']); ?> occupes</small>
                </article>
                <article class="stat-panel">
                    <span>Avis clients</span>
                    <strong><?= e((string) $metrics['reviews']); ?></strong>
                    <small>Retours publies</small>
                </article>
            </div>

            <section class="content-panel admin-order-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Reservations</span>
                        <h2>Dernieres demandes</h2>
                    </div>
                </div>

                <?php if ($recentReservations): ?>
                    <div class="admin-order-list">
                        <div class="admin-order-row admin-order-head">
                            <span>Client</span>
                            <span>Service</span>
                            <span>Date</span>
                            <span>Statut</span>
                        </div>
                        <?php foreach ($recentReservations as $index => $reservation): ?>
                            <article class="admin-order-row <?= $index === 0 ? 'is-featured' : ''; ?>">
                                <div class="order-client">
                                    <span class="client-avatar"><?= e(strtoupper(substr((string) ($reservation['prenom'] ?? 'U'), 0, 1))); ?></span>
                                    <strong><?= e(trim((string) (($reservation['prenom'] ?? '') . ' ' . ($reservation['nom'] ?? '')))); ?></strong>
                                </div>
                                <span><?= e((string) $reservation['service_nom']); ?></span>
                                <span><?= e(format_datetime((string) $reservation['date_slot'], 'd/m/Y')); ?> a <?= e(substr((string) $reservation['heure_debut'], 0, 5)); ?></span>
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

            <div class="admin-control-grid">
                <section class="content-panel quick-actions-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Actions rapides</span>
                            <h2>Gestion</h2>
                        </div>
                    </div>

                    <div class="quick-action-grid">
                        <a class="quick-action" href="<?= url('admin/services.php'); ?>">
                            <span>Services</span>
                            <strong>Gerer le catalogue</strong>
                        </a>
                        <a class="quick-action" href="<?= url('admin/reviews.php'); ?>">
                            <span>Avis</span>
                            <strong>Controler les retours</strong>
                        </a>
                    </div>
                </section>

                <section class="content-panel status-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Reservations</span>
                            <h2>Etat global</h2>
                        </div>
                    </div>

                    <div class="status-summary-grid">
                        <?php foreach ($reservationStatusCounts as $status => $total): ?>
                            <div class="status-summary-item">
                                <span class="<?= e(badge_class_for($status)); ?>"><?= e(reservation_status_label($status)); ?></span>
                                <strong><?= e((string) $total); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <div class="dashboard-columns admin-dashboard-columns">
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
                            <span class="small-label">Planning</span>
                            <h2>Prochains creneaux</h2>
                        </div>
                    </div>

                    <?php if ($nextSlots): ?>
                        <div class="slot-timeline">
                            <?php foreach ($nextSlots as $slot): ?>
                                <article class="slot-timeline-item">
                                    <div class="slot-date">
                                        <strong><?= e(format_datetime((string) $slot['date_slot'], 'd/m')); ?></strong>
                                        <span><?= e(substr((string) $slot['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $slot['heure_fin'], 0, 5)); ?></span>
                                    </div>
                                    <div>
                                        <strong><?= e((string) $slot['service_nom']); ?></strong>
                                        <p><?= e((string) $slot['categorie']); ?></p>
                                    </div>
                                    <span class="status-badge <?= ((int) $slot['disponible'] === 1) ? 'success' : 'muted'; ?>">
                                        <?= ((int) $slot['disponible'] === 1) ? 'Disponible' : 'Occupe'; ?>
                                    </span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucun creneau a venir pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
