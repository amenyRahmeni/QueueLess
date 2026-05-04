<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

$serviceId = (int) ($_GET['id'] ?? 0);

if ($serviceId <= 0) {
    set_flash_message('error', 'Service invalide.');
    redirect('admin/services.php');
}

$service = null;
$owners = [];

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'SELECT id, owner_id, nom, description, categorie, adresse, image, duree_moyenne, actif
        FROM services
        WHERE id = :id
        LIMIT 1'
    );
    $statement->execute([
        'id' => $serviceId,
    ]);
    $service = $statement->fetch();

    $ownersStatement = $pdo->query('SELECT id, nom, prenom, email FROM users WHERE role = "owner" ORDER BY nom ASC, prenom ASC');
    $owners = $ownersStatement->fetchAll();
} catch (Throwable $exception) {
    $service = null;
}

if (!$service) {
    set_flash_message('error', 'Service introuvable.');
    redirect('admin/services.php');
}

$pageTitle = 'Modifier Service';
$pageDescription = 'Modification d un service QueueLess.';
$currentPage = 'admin_services';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Edition service</span>
                <h1>Modifier <?= e((string) $service['nom']); ?></h1>
                <p>Mettez a jour les informations du service et remplacez l image si necessaire.</p>
            </div>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Edition</span>
                        <h2>Informations du service</h2>
                    </div>
                </div>

                <form class="admin-form" method="POST" action="<?= url('actions/edit_service_action.php'); ?>" enctype="multipart/form-data" data-validate>
                    <input type="hidden" name="service_id" value="<?= e((string) $service['id']); ?>">

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="nom">Nom du service</label>
                            <input type="text" id="nom" name="nom" value="<?= e((string) $service['nom']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="categorie">Categorie</label>
                            <input type="text" id="categorie" name="categorie" value="<?= e((string) $service['categorie']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="owner_id">Proprietaire du service</label>
                        <select id="owner_id" name="owner_id">
                            <option value="0">Non assigne</option>
                            <?php foreach ($owners as $owner): ?>
                                <option value="<?= e((string) $owner['id']); ?>" <?= (int) $service['owner_id'] === (int) $owner['id'] ? 'selected' : ''; ?>>
                                    <?= e(trim((string) (($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? '')))); ?> - <?= e((string) $owner['email']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required><?= e((string) $service['description']); ?></textarea>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" name="adresse" value="<?= e((string) $service['adresse']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="duree_moyenne">Duree moyenne (minutes)</label>
                            <input type="number" id="duree_moyenne" name="duree_moyenne" min="5" value="<?= e((string) $service['duree_moyenne']); ?>" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="image">Remplacer l image</label>
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                            <?php if (!empty($service['image'])): ?>
                                <div class="image-preview">
                                    <img src="<?= e(service_image_url((string) $service['image'])); ?>" alt="<?= e((string) $service['nom']); ?>">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-row">
                                <input type="checkbox" name="actif" <?= (int) $service['actif'] === 1 ? 'checked' : ''; ?>>
                                <span>Service actif</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
                        <a class="btn btn-secondary" href="<?= url('admin/services.php'); ?>">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
