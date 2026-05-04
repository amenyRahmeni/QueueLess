<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Ajouter Service';
$pageDescription = 'Ajout d un service QueueLess.';
$currentPage = 'admin_services';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];
$owners = [];

try {
    $pdo = getPDO();
    $ownersStatement = $pdo->query('SELECT id, nom, prenom, email FROM users WHERE role = "owner" ORDER BY nom ASC, prenom ASC');
    $owners = $ownersStatement->fetchAll();
} catch (Throwable $exception) {
    $owners = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Nouveau service</span>
                <h1>Ajouter un service</h1>
                <p>Renseignez les informations principales du service et ajoutez une image si necessaire.</p>
            </div>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Formulaire</span>
                        <h2>Informations du service</h2>
                    </div>
                </div>

                <form class="admin-form" method="POST" action="<?= url('actions/add_service_action.php'); ?>" enctype="multipart/form-data" data-validate>
                    <div class="field-grid">
                        <div class="form-group">
                            <label for="nom">Nom du service</label>
                            <input type="text" id="nom" name="nom" value="<?= e(old('nom')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="categorie">Categorie</label>
                            <input type="text" id="categorie" name="categorie" value="<?= e(old('categorie')); ?>" placeholder="Banque, Restaurant..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="owner_id">Proprietaire du service</label>
                        <select id="owner_id" name="owner_id">
                            <option value="0">Non assigne</option>
                            <?php foreach ($owners as $owner): ?>
                                <option value="<?= e((string) $owner['id']); ?>" <?= (int) old('owner_id', '0') === (int) $owner['id'] ? 'selected' : ''; ?>>
                                    <?= e(trim((string) (($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? '')))); ?> - <?= e((string) $owner['email']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required><?= e(old('description')); ?></textarea>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" name="adresse" value="<?= e(old('adresse')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="duree_moyenne">Duree moyenne (minutes)</label>
                            <input type="number" id="duree_moyenne" name="duree_moyenne" min="5" value="<?= e(old('duree_moyenne', '30')); ?>" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-group">
                            <label for="image">Image du service</label>
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-row">
                                <input type="checkbox" name="actif" <?= old('actif', '1') !== '' ? 'checked' : ''; ?>>
                                <span>Service actif des sa creation</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Enregistrer le service</button>
                        <a class="btn btn-secondary" href="<?= url('admin/services.php'); ?>">Annuler</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
