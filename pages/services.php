<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Services';
$pageDescription = 'Parcourez les services disponibles sur QueueLess.';
$currentPage = 'services';

$search = trim((string) ($_GET['search'] ?? ''));
$categorie = trim((string) ($_GET['categorie'] ?? ''));
$services = [];
$categories = [];
$databaseWarning = null;

try {
    $pdo = getPDO();

    $categoryStatement = $pdo->query(
        'SELECT DISTINCT categorie
        FROM services
        WHERE actif = 1
        ORDER BY categorie ASC'
    );
    $categories = $categoryStatement->fetchAll();

    $conditions = ['s.actif = 1'];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(s.nom LIKE :search OR s.description LIKE :search OR s.adresse LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    if ($categorie !== '') {
        $conditions[] = 's.categorie = :categorie';
        $params['categorie'] = $categorie;
    }

    $sql = 'SELECT
            s.id,
            s.nom,
            s.description,
            s.categorie,
            s.adresse,
            s.image,
            s.duree_moyenne,
            (
                SELECT COUNT(*)
                FROM slots sl
                WHERE sl.service_id = s.id
                    AND sl.disponible = 1
                    AND sl.date_slot >= CURDATE()
            ) AS available_slots,
            (
                SELECT AVG(a.note)
                FROM avis a
                WHERE a.service_id = s.id
            ) AS average_note
        FROM services s
        WHERE ' . implode(' AND ', $conditions) . '
        ORDER BY s.created_at DESC';

    $servicesStatement = $pdo->prepare($sql);
    $servicesStatement->execute($params);
    $services = $servicesStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'La base de donnees n est pas encore disponible. Importez le script SQL pour afficher les services.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="catalog-section">
    <div class="container">
        <div class="catalog-hero">
            <span class="section-badge catalog-badge">Catalogue QueueLess</span>
            <h1>Des services organises pour reserver sans attendre</h1>
            <p>Explorez les services disponibles, filtrez par categorie et ouvrez la fiche detaillee de chaque service.</p>
        </div>

        <?php if ($databaseWarning): ?>
            <div class="alert alert-warning">
                <span><?= e($databaseWarning); ?></span>
            </div>
        <?php endif; ?>

        <section class="catalog-filter-panel">
            <form class="catalog-filters" method="GET" action="<?= url('pages/services.php'); ?>">
                <div class="catalog-field">
                    <label for="search">Recherche</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="<?= e($search); ?>"
                        placeholder="Nom, description ou adresse"
                        data-filter-input="#services-grid"
                    >
                </div>

                <div class="catalog-field">
                    <label for="categorie">Categorie</label>
                    <select id="categorie" name="categorie">
                        <option value="">Toutes les categories</option>
                        <?php foreach ($categories as $categoryItem): ?>
                            <?php $categoryName = (string) ($categoryItem['categorie'] ?? ''); ?>
                            <option value="<?= e($categoryName); ?>" <?= $categoryName === $categorie ? 'selected' : ''; ?>>
                                <?= e($categoryName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="catalog-actions">
                    <button class="btn btn-primary" type="submit">Filtrer</button>
                    <a class="btn btn-secondary" href="<?= url('pages/services.php'); ?>">Reinitialiser</a>
                </div>
            </form>
        </section>

        <div class="services-grid" id="services-grid">
            <?php if ($services): ?>
                <?php foreach ($services as $service): ?>
                    <?php
                    $serviceName = (string) ($service['nom'] ?? '');
                    $serviceDescription = (string) ($service['description'] ?? '');
                    $serviceAddress = (string) ($service['adresse'] ?? '');
                    $serviceCategory = (string) ($service['categorie'] ?? '');
                    $availableSlots = (int) ($service['available_slots'] ?? 0);
                    $averageNote = $service['average_note'] !== null ? number_format((float) $service['average_note'], 1) : null;
                    ?>
                    <article
                        class="service-catalog-card"
                        data-filter-item
                        data-search-text="<?= e(strtolower($serviceName . ' ' . $serviceDescription . ' ' . $serviceAddress . ' ' . $serviceCategory)); ?>"
                    >
                        <div class="service-catalog-media">
                            <img src="<?= e(service_image_url((string) ($service['image'] ?? ''))); ?>" alt="<?= e($serviceName); ?>">
                        </div>

                        <div class="service-catalog-body">
                            <div class="service-topline">
                                <span class="status-badge"><?= e($serviceCategory); ?></span>
                                <?php if ($averageNote !== null): ?>
                                    <span class="service-rating">Note <?= e($averageNote); ?>/5</span>
                                <?php endif; ?>
                            </div>

                            <h2><?= e($serviceName); ?></h2>
                            <p><?= e(strlen($serviceDescription) > 170 ? substr($serviceDescription, 0, 167) . '...' : $serviceDescription); ?></p>

                            <div class="service-info-list">
                                <span><?= e($serviceAddress); ?></span>
                                <span>Duree moyenne: <?= e((string) ($service['duree_moyenne'] ?? 0)); ?> min</span>
                                <span><?= e((string) $availableSlots); ?> creneau(x) disponible(s)</span>
                            </div>

                            <div class="service-card-actions">
                                <a class="btn btn-primary" href="<?= url('pages/service_details.php?id=' . (int) ($service['id'] ?? 0)); ?>">
                                    Voir les details
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="catalog-empty">
                    <h2>Aucun service trouve</h2>
                    <p>Essayez une autre recherche ou reinitialisez les filtres pour voir tous les services.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
