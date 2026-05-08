<?php
declare(strict_types=1);


require_once __DIR__ . '/../includes/owner_check.php';


$slotId = (int) ($_GET['id'] ?? 0);

if ($slotId <= 0) {
    set_flash_message('error', 'Creneau invalide.');
    redirect('owner/slots.php');
}

$slot = null;
$services = [];
$ownerId = (int) (current_user()['id'] ?? 0);

try {
    $pdo = getPDO();
    $servicesStatement = $pdo->prepare('SELECT id, nom FROM services WHERE owner_id = :owner_id ORDER BY nom ASC');
    $servicesStatement->execute(['owner_id' => $ownerId]);
    $services = $servicesStatement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT sl.id, sl.service_id, sl.date_slot, sl.heure_debut, sl.heure_fin, sl.disponible
        FROM slots sl
        INNER JOIN services s ON s.id = sl.service_id
        WHERE sl.id = :id AND s.owner_id = :owner_id
        LIMIT 1'
    );
    $statement->execute([
        'id' => $slotId,
        'owner_id' => $ownerId,
    ]);
    $slot = $statement->fetch();
} catch (Throwable $exception) {
    $slot = null;
}

if (!$slot) {
    set_flash_message('error', 'Creneau introuvable.');
    redirect('owner/slots.php');
}

$pageTitle = 'Modifier Creneau';
$pageDescription = 'Modification d un creneau de service.';
$currentPage = 'owner_slots';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Edition creneau</span>
                <h1>Modifier un creneau</h1>
                <p>Mettez a jour la date, les horaires et la disponibilite.</p>
            </div>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Edition</span>
                        <h2>Informations du creneau</h2>
                    </div>
                </div>

                <form class="admin-form" method="POST" action="<?= url('actions/edit_slot_action.php'); ?>" data-validate>
                    <input type="hidden" name="slot_id" value="<?= e((string) $slot['id']); ?>">

                    <div class="form-group">
                        <label for="service_id">Service</label>
                        <select id="service_id" name="service_id" required>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= e((string) $service['id']); ?>" <?= (int) $service['id'] === (int) $slot['service_id'] ? 'selected' : ''; ?>>
                                    <?= e((string) $service['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="date_slot">Date</label>
                            <input type="date" id="date_slot" name="date_slot" value="<?= e((string) $slot['date_slot']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="heure_debut">Heure debut</label>
                            <input type="time" id="heure_debut" name="heure_debut" value="<?= e(substr((string) $slot['heure_debut'], 0, 5)); ?>" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="heure_fin">Heure fin</label>
                            <input type="time" id="heure_fin" name="heure_fin" value="<?= e(substr((string) $slot['heure_fin'], 0, 5)); ?>" required>
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="checkbox-row">
                                <input type="checkbox" name="disponible" <?= (int) $slot['disponible'] === 1 ? 'checked' : ''; ?>>
                                <span>Creneau disponible</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
                        <a class="btn btn-secondary" href="<?= url('owner/slots.php'); ?>">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
