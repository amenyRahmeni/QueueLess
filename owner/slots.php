<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/owner_check.php';

$pageTitle = 'Mes creneaux';
$pageDescription = 'Gestion des creneaux du proprietaire de service.';
$currentPage = 'owner_slots';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$ownerId = (int) (current_user()['id'] ?? 0);
$serviceFilter = (int) ($_GET['service_id'] ?? 0);
$slots = [];
$services = [];
$databaseWarning = null;

try {
    $pdo = getPDO();

    $servicesStatement = $pdo->prepare('SELECT id, nom FROM services WHERE owner_id = :owner_id ORDER BY nom ASC');
    $servicesStatement->execute(['owner_id' => $ownerId]);
    $services = $servicesStatement->fetchAll();

    $sql = 'SELECT
            sl.id,
            sl.service_id,
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin,
            sl.disponible,
            s.nom AS service_nom
        FROM slots sl
        INNER JOIN services s ON s.id = sl.service_id
        WHERE s.owner_id = :owner_id';

    $params = ['owner_id' => $ownerId];
    if ($serviceFilter > 0) {
        $sql .= ' AND sl.service_id = :service_id';
        $params['service_id'] = $serviceFilter;
    }

    $sql .= ' ORDER BY sl.date_slot DESC, sl.heure_debut DESC';

    $slotsStatement = $pdo->prepare($sql);
    $slotsStatement->execute($params);
    $slots = $slotsStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger vos creneaux pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Planning service</span>
                <h1>Mes creneaux</h1>
                <p>Ajoutez et ajustez les disponibilites de vos services.</p>
            </div>

            <div class="admin-control-grid">
                <section class="content-panel quick-actions-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Services</span>
                            <h2>Avant les creneaux</h2>
                        </div>
                    </div>

                    <div class="quick-action-grid">
                        <a class="quick-action" href="<?= url('owner/services.php'); ?>">
                            <span>Mes services</span>
                            <strong>Voir les services que je gere</strong>
                        </a>
                        <a class="quick-action" href="<?= url('owner/add_service.php'); ?>">
                            <span>Nouveau</span>
                            <strong>Ajouter un service</strong>
                        </a>
                    </div>
                </section>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Planning</span>
                        <h2>Liste des creneaux</h2>
                    </div>
                    <div class="table-actions">
                        <form class="inline-filter" method="GET" action="<?= url('owner/slots.php'); ?>">
                            <select name="service_id" onchange="this.form.submit()">
                                <option value="0">Tous mes services</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= e((string) $service['id']); ?>" <?= (int) $service['id'] === $serviceFilter ? 'selected' : ''; ?>>
                                        <?= e((string) $service['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <a class="btn btn-primary" href="<?= url('owner/add_slot.php'); ?>">Ajouter un creneau</a>
                    </div>
                </div>

                <?php if ($slots): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Disponibilite</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slots as $slot): ?>
                                    <tr>
                                        <td><?= e((string) $slot['service_nom']); ?></td>
                                        <td><?= e(format_datetime((string) $slot['date_slot'], 'd/m/Y')); ?></td>
                                        <td><?= e(substr((string) $slot['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $slot['heure_fin'], 0, 5)); ?></td>
                                        <td>
                                            <span class="<?= e(badge_class_for((int) $slot['disponible'] === 1 ? 'disponible' : 'occupe')); ?>">
                                                <?= e((int) $slot['disponible'] === 1 ? 'Disponible' : 'Occupe'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="table-link" href="<?= url('owner/edit_slot.php?id=' . (int) $slot['id']); ?>">Modifier</a>
                                                <form method="POST" action="<?= url('actions/delete_slot_action.php'); ?>">
                                                    <input type="hidden" name="slot_id" value="<?= e((string) $slot['id']); ?>">
                                                    <button class="table-button danger" type="submit" data-confirm="Supprimer ce creneau ?">
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
                        <p>Aucun creneau disponible pour le moment.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
