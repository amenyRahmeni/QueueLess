<?php
declare(strict_types=1);

$serviceId = (int) ($_GET['id'] ?? 0);

if ($serviceId <= 0) {
    require_once __DIR__ . '/../includes/functions.php';
    set_flash_message('error', 'Service introuvable.');
    redirect('pages/services.php');
}

$service = null;
$slots = [];
$reviews = [];
$databaseWarning = null;

try {
    require_once __DIR__ . '/../includes/functions.php';
    $pdo = getPDO();

    $serviceStatement = $pdo->prepare(
        'SELECT
            s.id,
            s.nom,
            s.description,
            s.categorie,
            s.adresse,
            s.image,
            s.duree_moyenne,
            s.actif,
            (
                SELECT AVG(a.note)
                FROM avis a
                WHERE a.service_id = s.id
            ) AS average_note,
            (
                SELECT COUNT(*)
                FROM avis a
                WHERE a.service_id = s.id
            ) AS total_reviews,
            (
                SELECT COUNT(*)
                FROM slots sl
                WHERE sl.service_id = s.id
                    AND sl.disponible = 1
                    AND sl.date_slot >= CURDATE()
            ) AS available_slots
        FROM services s
        WHERE s.id = :id
            AND s.actif = 1
        LIMIT 1'
    );
    $serviceStatement->execute([
        'id' => $serviceId,
    ]);
    $service = $serviceStatement->fetch();

    if (!$service) {
        set_flash_message('error', 'Service introuvable ou inactif.');
        redirect('pages/services.php');
    }

    $slotsStatement = $pdo->prepare(
        'SELECT id, date_slot, heure_debut, heure_fin, disponible
        FROM slots
        WHERE service_id = :service_id
            AND date_slot >= CURDATE()
        ORDER BY date_slot ASC, heure_debut ASC
        LIMIT 12'
    );
    $slotsStatement->execute([
        'service_id' => $serviceId,
    ]);
    $slots = $slotsStatement->fetchAll();

    $reviewsStatement = $pdo->prepare(
        'SELECT a.note, a.commentaire, a.created_at, u.prenom, u.nom
        FROM avis a
        INNER JOIN users u ON u.id = a.user_id
        WHERE a.service_id = :service_id
        ORDER BY a.created_at DESC
        LIMIT 3'
    );
    $reviewsStatement->execute([
        'service_id' => $serviceId,
    ]);
    $reviews = $reviewsStatement->fetchAll();
} catch (Throwable $exception) {
    if (!function_exists('e')) {
        require_once __DIR__ . '/../includes/functions.php';
    }

    $databaseWarning = 'La base de donnees n est pas encore disponible. Importez le script SQL pour afficher les details du service.';
}

$pageTitle = $service ? (string) $service['nom'] : 'Details du service';
$pageDescription = $service ? (string) $service['description'] : 'Details d un service QueueLess.';
$currentPage = 'services';

require_once __DIR__ . '/../includes/header.php';
?>

<section class="service-detail-section">
    <div class="container">
        <?php if ($databaseWarning): ?>
            <div class="alert alert-warning">
                <span><?= e($databaseWarning); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($service): ?>
            <?php
            $averageNote = $service['average_note'] !== null ? number_format((float) $service['average_note'], 1) : null;
            $availableSlots = (int) ($service['available_slots'] ?? 0);
            ?>
            <div class="service-detail-card">
                <div class="service-detail-media">
                    <img src="<?= e(service_image_url((string) ($service['image'] ?? ''))); ?>" alt="<?= e((string) $service['nom']); ?>">
                </div>

                <div class="service-detail-content">
                    <span class="section-badge catalog-badge"><?= e((string) $service['categorie']); ?></span>
                    <h1><?= e((string) $service['nom']); ?></h1>
                    <p class="service-detail-text"><?= e((string) $service['description']); ?></p>

                    <div class="service-detail-meta">
                        <div class="meta-pill">
                            <span>Adresse</span>
                            <strong><?= e((string) $service['adresse']); ?></strong>
                        </div>
                        <div class="meta-pill">
                            <span>Duree moyenne</span>
                            <strong><?= e((string) ($service['duree_moyenne'] ?? 0)); ?> min</strong>
                        </div>
                        <div class="meta-pill">
                            <span>Creneaux ouverts</span>
                            <strong><?= e((string) $availableSlots); ?></strong>
                        </div>
                        <div class="meta-pill">
                            <span>Avis</span>
                            <strong><?= e($averageNote !== null ? $averageNote . '/5' : 'Aucun'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="service-detail-grid">
                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Reservation</span>
                            <h2>Creneaux disponibles</h2>
                        </div>
                    </div>

                    <?php if ($slots): ?>
                        <div class="slot-card-grid">
                            <?php foreach ($slots as $slot): ?>
                                <?php $slotAvailable = (int) ($slot['disponible'] ?? 0) === 1; ?>
                                <article class="slot-card <?= $slotAvailable ? '' : 'is-disabled'; ?>">
                                    <div>
                                        <strong><?= e(format_datetime((string) $slot['date_slot'], 'd/m/Y')); ?></strong>
                                        <p><?= e(substr((string) $slot['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $slot['heure_fin'], 0, 5)); ?></p>
                                    </div>

                                    <span class="<?= e($slotAvailable ? badge_class_for('disponible') : badge_class_for('occupe')); ?>">
                                        <?= e($slotAvailable ? 'Disponible' : 'Occupe'); ?>
                                    </span>

                                    <?php if ($slotAvailable): ?>
                                        <?php if (is_logged_in()): ?>
                                            <form method="POST" action="<?= url('actions/reserve_slot.php'); ?>">
                                                <input type="hidden" name="service_id" value="<?= e((string) $serviceId); ?>">
                                                <input type="hidden" name="slot_id" value="<?= e((string) ($slot['id'] ?? 0)); ?>">
                                                <button class="btn btn-primary slot-action" type="submit">Reserver ce creneau</button>
                                            </form>
                                        <?php else: ?>
                                            <a class="btn btn-secondary slot-action" href="<?= url('pages/login.php'); ?>">Se connecter pour reserver</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucun creneau n est disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Avis recents</span>
                            <h2>Ce que disent les utilisateurs</h2>
                        </div>
                    </div>

                    <?php if ($reviews): ?>
                        <div class="review-list">
                            <?php foreach ($reviews as $review): ?>
                                <article class="review-card">
                                    <div class="review-top">
                                        <strong><?= e(trim((string) (($review['prenom'] ?? '') . ' ' . ($review['nom'] ?? '')))); ?></strong>
                                        <span class="service-rating">Note <?= e((string) $review['note']); ?>/5</span>
                                    </div>
                                    <p><?= e((string) ($review['commentaire'] ?? '')); ?></p>
                                    <small><?= e(format_datetime((string) ($review['created_at'] ?? ''), 'd/m/Y')); ?></small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-panel">
                            <p>Aucun avis n a encore ete publie pour ce service.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        <?php else: ?>
            <div class="catalog-empty">
                <h1>Details du service indisponibles</h1>
                <p>Le service ne peut pas etre affiche pour le moment. Verifiez la base de donnees puis reessayez.</p>
                <a class="btn btn-primary" href="<?= url('pages/services.php'); ?>">Retour aux services</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
