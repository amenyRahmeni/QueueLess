<?php
declare(strict_types=1);


require_once __DIR__ . '/../includes/owner_check.php';


$pageTitle = 'Mes services';
$pageDescription = 'Gestion des services du proprietaire.';
$currentPage = 'owner_services';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$ownerId = (int) (current_user()['id'] ?? 0);
$services = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'SELECT
            s.id,
            s.nom,
            s.categorie,
            s.adresse,
            s.actif,
            s.duree_moyenne,
            (
                SELECT COUNT(*)
                FROM slots sl
                WHERE sl.service_id = s.id
            ) AS total_slots
        FROM services s
        WHERE s.owner_id = :owner_id
        ORDER BY s.created_at DESC'
    );
    $statement->execute(['owner_id' => $ownerId]);
    $services = $statement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger vos services pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Espace service</span>
                <h1>Mes services</h1>
                <p>Ajoutez vos services, puis creez les creneaux disponibles pour chaque service.</p>
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
                        <h2>Services que vous gerez</h2>
                    </div>
                    <a class="btn btn-primary" href="<?= url('owner/add_service.php'); ?>">Ajouter un service</a>
                </div>

                <?php if ($services): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Categorie</th>
                                    <th>Adresse</th>
                                    <th>Duree</th>
                                    <th>Creneaux</th>
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
                                        <td><?= e((string) $service['duree_moyenne']); ?> min</td>
                                        <td><?= e((string) $service['total_slots']); ?></td>
                                        <td>
                                            <span class="<?= e(badge_class_for((int) $service['actif'] === 1 ? 'actif' : 'inactif')); ?>">
                                                <?= e((int) $service['actif'] === 1 ? 'Actif' : 'Inactif'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="table-link" href="<?= url('owner/slots.php?service_id=' . (int) $service['id']); ?>">Creneaux</a>
                                                <a class="table-link" href="<?= url('owner/add_slot.php'); ?>">Ajouter creneau</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>Vous n avez pas encore ajoute de service.</p>
                        <a class="btn btn-primary" href="<?= url('owner/add_service.php'); ?>">Ajouter mon premier service</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
