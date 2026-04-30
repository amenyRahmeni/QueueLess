<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$reservationId = (int) ($_GET['reservation_id'] ?? 0);

if ($reservationId <= 0) {
    set_flash_message('error', 'Reservation invalide pour laisser un avis.');
    redirect('pages/my_reservations.php');
}

$pageTitle = 'Ajouter un avis';
$pageDescription = 'Ajoutez ou modifiez votre avis sur un service QueueLess.';
$currentPage = 'reservations';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$reviewTarget = null;
$databaseWarning = null;

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'SELECT
            r.id AS reservation_id,
            r.service_id,
            r.statut,
            s.nom AS service_nom,
            s.categorie,
            s.adresse,
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin,
            a.id AS review_id,
            a.note,
            a.commentaire,
            CASE
                WHEN r.statut IN ("reservee", "terminee") AND TIMESTAMP(sl.date_slot, sl.heure_fin) <= NOW() THEN 1
                ELSE 0
            END AS can_review
        FROM reservations r
        INNER JOIN services s ON s.id = r.service_id
        INNER JOIN slots sl ON sl.id = r.slot_id
        LEFT JOIN avis a ON a.user_id = r.user_id AND a.service_id = r.service_id
        WHERE r.id = :reservation_id
            AND r.user_id = :user_id
        LIMIT 1'
    );
    $statement->execute([
        'reservation_id' => $reservationId,
        'user_id' => $userId,
    ]);
    $reviewTarget = $statement->fetch();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger les informations de cette reservation pour le moment.';
}

if (!$reviewTarget) {
    set_flash_message('error', 'Reservation introuvable.');
    redirect('pages/my_reservations.php');
}

if ((int) ($reviewTarget['can_review'] ?? 0) !== 1) {
    set_flash_message('warning', 'Vous pouvez laisser un avis seulement apres la fin de la reservation.');
    redirect('pages/my_reservations.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_user.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Avis utilisateur</span>
                <h1><?= (int) ($reviewTarget['review_id'] ?? 0) > 0 ? 'Modifier votre avis' : 'Laisser un avis'; ?></h1>
                <p>Partagez votre retour d experience pour aider les prochains utilisateurs de QueueLess.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <div class="profile-grid">
                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Reservation concernee</span>
                            <h2><?= e((string) $reviewTarget['service_nom']); ?></h2>
                        </div>
                    </div>

                    <div class="summary-stack">
                        <div class="summary-line">
                            <span>Categorie</span>
                            <strong><?= e((string) $reviewTarget['categorie']); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Adresse</span>
                            <strong><?= e((string) $reviewTarget['adresse']); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Date</span>
                            <strong><?= e(format_datetime((string) $reviewTarget['date_slot'], 'd/m/Y')); ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Heure</span>
                            <strong><?= e(substr((string) $reviewTarget['heure_debut'], 0, 5)); ?> - <?= e(substr((string) $reviewTarget['heure_fin'], 0, 5)); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="content-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="small-label">Votre retour</span>
                            <h2>Formulaire d avis</h2>
                        </div>
                    </div>

                    <form class="admin-form" method="POST" action="<?= url('actions/add_review_action.php'); ?>" data-validate>
                        <input type="hidden" name="reservation_id" value="<?= e((string) $reviewTarget['reservation_id']); ?>">
                        <input type="hidden" name="service_id" value="<?= e((string) $reviewTarget['service_id']); ?>">

                        <div class="form-group">
                            <label for="note">Note</label>
                            <select id="note" name="note" required>
                                <option value="">Selectionner une note</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php $selected = old('note', (string) ($reviewTarget['note'] ?? '')) === (string) $i; ?>
                                    <option value="<?= e((string) $i); ?>" <?= $selected ? 'selected' : ''; ?>>
                                        <?= e((string) $i); ?>/5
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="commentaire">Commentaire</label>
                            <textarea id="commentaire" name="commentaire" rows="6" placeholder="Decrivez votre experience..." required><?= e(old('commentaire', (string) ($reviewTarget['commentaire'] ?? ''))); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit">
                                <?= (int) ($reviewTarget['review_id'] ?? 0) > 0 ? 'Mettre a jour l avis' : 'Publier l avis'; ?>
                            </button>
                            <a class="btn btn-secondary" href="<?= url('pages/my_reservations.php'); ?>">Retour</a>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

