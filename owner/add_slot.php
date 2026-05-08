<?php
declare(strict_types=1);


require_once __DIR__ . '/../includes/owner_check.php';


$pageTitle = 'Ajouter Creneau';
$pageDescription = 'Ajout d un creneau de service.';
$currentPage = 'owner_slots';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$services = [];

try {
    $pdo = getPDO();
    $statement = $pdo->prepare('SELECT id, nom FROM services WHERE owner_id = :owner_id ORDER BY nom ASC');
    $statement->execute(['owner_id' => (int) (current_user()['id'] ?? 0)]);
    $services = $statement->fetchAll();
} catch (Throwable $exception) {
    $services = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Nouveau creneau</span>
                <h1>Ajouter un creneau</h1>
                <p>Ajoutez une disponibilite pour l un de vos services.</p>
            </div>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Formulaire</span>
                        <h2>Informations du creneau</h2>
                    </div>
                </div>

                <form class="admin-form" method="POST" action="<?= url('actions/add_slot_action.php'); ?>" data-validate>
                    <div class="form-group">
                        <label for="service_id">Service</label>
                        <select id="service_id" name="service_id" required>
                            <option value="">Choisir un service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= e((string) $service['id']); ?>"><?= e((string) $service['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="date_slot">Date</label>
                            <input type="date" id="date_slot" name="date_slot" required>
                        </div>
                        <div class="form-group">
                            <label for="heure_debut">Heure debut</label>
                            <input type="time" id="heure_debut" name="heure_debut" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="heure_fin">Heure fin</label>
                            <input type="time" id="heure_fin" name="heure_fin" required>
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="checkbox-row">
                                <input type="checkbox" name="disponible" checked>
                                <span>Creneau disponible</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Enregistrer le creneau</button>
                        <a class="btn btn-secondary" href="<?= url('owner/slots.php'); ?>">Annuler</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
