<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

set_flash_message('warning', 'La gestion des creneaux est maintenant reservee au proprietaire du service.');
redirect('admin/services.php');

$pageTitle = 'Ajouter Creneau';
$pageDescription = 'Ajout d un creneau QueueLess.';
$currentPage = 'admin_slots';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$services = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $servicesStatement = $pdo->query('SELECT id, nom FROM services WHERE actif = 1 ORDER BY nom ASC');
    $services = $servicesStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger les services actifs pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Nouveau creneau</span>
                <h1>Ajouter un creneau</h1>
                <p>Planifiez un nouveau rendez-vous disponible pour un service actif.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Formulaire</span>
                        <h2>Informations du creneau</h2>
                    </div>
                </div>

                <form class="admin-form" method="POST" action="<?= url('actions/add_slot_action.php'); ?>" data-validate>
                    <div class="field-grid">
                        <div class="form-group">
                            <label for="service_id">Service</label>
                            <select id="service_id" name="service_id" required>
                                <option value="">Selectionner un service</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= e((string) $service['id']); ?>"><?= e((string) $service['nom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-row">
                                <input type="checkbox" name="disponible" checked>
                                <span>Creneau disponible a la creation</span>
                            </label>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="date_slot">Date</label>
                            <input type="date" id="date_slot" name="date_slot" required>
                        </div>

                        <div class="form-group">
                            <label for="heure_debut">Heure de debut</label>
                            <input type="time" id="heure_debut" name="heure_debut" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="heure_fin">Heure de fin</label>
                            <input type="time" id="heure_fin" name="heure_fin" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Enregistrer le creneau</button>
                        <a class="btn btn-secondary" href="<?= url('admin/slots.php'); ?>">Annuler</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
