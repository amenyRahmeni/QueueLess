<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';


$pageTitle = 'Statistiques Admin';
$pageDescription = 'Statistiques QueueLess.';
$currentPage = 'admin_stats';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$stats = [
    'users' => 0,
    'services' => 0,
    'reservations' => 0,
    'available_slots' => 0,
    'occupied_slots' => 0,
];
$topServices = [];
$categories = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $stats['users'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['services'] = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $stats['reservations'] = (int) $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    $stats['available_slots'] = (int) $pdo->query('SELECT COUNT(*) FROM slots WHERE disponible = 1')->fetchColumn();
    $stats['occupied_slots'] = (int) $pdo->query('SELECT COUNT(*) FROM slots WHERE disponible = 0')->fetchColumn();

    $topServicesStatement = $pdo->query(
        'SELECT s.nom, s.categorie, COUNT(r.id) AS total_reservations
        FROM services s
        LEFT JOIN reservations r ON r.service_id = s.id
        GROUP BY s.id, s.nom, s.categorie
        ORDER BY total_reservations DESC, s.nom ASC
        LIMIT 6'
    );
    $topServices = $topServicesStatement->fetchAll();

    $categoriesStatement = $pdo->query(
        'SELECT categorie, COUNT(*) AS total_services
        FROM services
        GROUP BY categorie
        ORDER BY total_services DESC, categorie ASC'
    );
    $categories = $categoriesStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de calculer les statistiques pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Analyse globale</span>
                <h1>Statistiques</h1>
                <p>Consultez les principaux indicateurs de QueueLess et les services les plus actifs.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <div class="stats-grid admin-stats-grid">
                <article class="stat-panel">
                    <span>Utilisateurs</span>
                    <strong><?= e((string) $stats['users']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Services</span>
                    <strong><?= e((string) $stats['services']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Reservations</span>
                    <strong><?= e((string) $stats['reservations']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Slots disponibles</span>
                    <strong><?= e((string) $stats['available_slots']); ?></strong>
                </article>
                <article class="stat-panel">
                    <span>Slots occupes</span>
                    <strong><?= e((string) $stats['occupied_slots']); ?></strong>
                </article>
            </div>

            <div class="dashboard-columns">
                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Performance</span>
                            <h2>Services les plus reserves</h2>
                        </div>
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
                            <p>Aucune statistique de reservation disponible.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Repartition</span>
                            <h2>Services par categorie</h2>
                        </div>
                    </div>

                    <?php if ($categories): ?>
                        <div class="activity-list">
                            <?php foreach ($categories as $category): ?>
                                <article class="activity-item">
                                    <div>
                                        <strong><?= e((string) $category['categorie']); ?></strong>
                                    </div>
                                    <span class="status-badge"><?= e((string) $category['total_services']); ?> service(s)</span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucune categorie disponible.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
