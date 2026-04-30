<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

$slotId = (int) ($_GET['id'] ?? 0);

if ($slotId <= 0) {
    set_flash_message('error', 'Creneau invalide.');
    redirect('admin/slots.php');
}

$slot = null;
$services = [];

try {
    $pdo = getPDO();

    $servicesStatement = $pdo->query('SELECT id, nom FROM services ORDER BY nom ASC');
    $services = $servicesStatement->fetchAll();

    $slotStatement = $pdo->prepare(
        'SELECT id, service_id, date_slot, heure_debut, heure_fin, disponible
        FROM slots
        WHERE id = :id
        LIMIT 1'
    );
    $slotStatement->execute([
        'id' => $slotId,
    ]);
    $slot = $slotStatement->fetch();
} catch (Throwable $exception) {
    $slot = null;
}

if (!$slot) {
    set_flash_message('error', 'Creneau introuvable.');
    redirect('admin/slots.php');
}

$pageTitle = 'Modifier Creneau';
$pageDescription = 'Modification d un creneau QueueLess.';
$currentPage = 'admin_slots';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Edition creneau</span>
                <h1>Modifier un creneau</h1>
                <p>Ajustez la date, l horaire ou la disponibilite du creneau selectionne.</p>
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

                    <div class="field-grid">
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

                        <div class="form-group checkbox-group">
                            <label class="checkbox-row">
                                <input type="checkbox" name="disponible" <?= (int) $slot['disponible'] === 1 ? 'checked' : ''; ?>>
                                <span>Creneau disponible</span>
                            </label>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="date_slot">Date</label>
                            <input type="date" id="date_slot" name="date_slot" value="<?= e((string) $slot['date_slot']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="heure_debut">Heure de debut</label>
                            <input type="time" id="heure_debut" name="heure_debut" value="<?= e(substr((string) $slot['heure_debut'], 0, 5)); ?>" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="heure_fin">Heure de fin</label>
                            <input type="time" id="heure_fin" name="heure_fin" value="<?= e(substr((string) $slot['heure_fin'], 0, 5)); ?>" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Mettre a jour le creneau</button>
                        <a class="btn btn-secondary" href="<?= url('admin/slots.php'); ?>">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

