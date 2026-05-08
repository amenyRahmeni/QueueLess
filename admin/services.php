<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';



$pageTitle = 'Services Admin';
$pageDescription = 'Gestion des services QueueLess.';
$currentPage = 'admin_services';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$services = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $servicesStatement = $pdo->query(
        'SELECT
            s.id,
            s.nom,
            s.categorie,
            s.adresse,
            s.actif,
            s.duree_moyenne,
            CONCAT(COALESCE(u.prenom, ""), " ", COALESCE(u.nom, "")) AS owner_name,
            (
                SELECT COUNT(*)
                FROM slots sl
                WHERE sl.service_id = s.id
            ) AS total_slots
        FROM services s
        LEFT JOIN users u ON u.id = s.owner_id
        ORDER BY s.created_at DESC'
    );
    $services = $servicesStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger les services pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Catalogue admin</span>
                <h1>Services</h1>
                <p>Ajoutez, modifiez et supprimez les services proposes sur la plateforme.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Catalogue</span>
                        <h2>Services enregistres</h2>
                    </div>
                </div>

                <?php if ($services): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Categorie</th>
                                    <th>Adresse</th>
                                    <th>Proprietaire</th>
                                    <th>Duree</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?= e((string) $service['nom']); ?></td>
                                        <td><?= e((string) $service['categorie']); ?></td>
                                        <td><?= e((string) $service['adresse']); ?></td>
                                        <td><?= e(trim((string) $service['owner_name']) !== '' ? trim((string) $service['owner_name']) : 'Non assigne'); ?></td>
                                        <td><?= e((string) $service['duree_moyenne']); ?> min</td>
                                        <td>
                                            <span class="<?= e(badge_class_for((int) $service['actif'] === 1 ? 'actif' : 'inactif')); ?>">
                                                <?= e((int) $service['actif'] === 1 ? 'Actif' : 'Inactif'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="table-link" href="<?= url('admin/edit_service.php?id=' . (int) $service['id']); ?>">Modifier</a>
                                                <form method="POST" action="<?= url('actions/delete_service_action.php'); ?>">
                                                    <input type="hidden" name="service_id" value="<?= e((string) $service['id']); ?>">
                                                    <button class="table-button danger" type="submit" data-confirm="Supprimer ce service ? Cette action peut echouer si des reservations existent.">
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>Aucun service disponible pour le moment.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
