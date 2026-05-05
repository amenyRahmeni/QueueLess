<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';


$pageTitle = 'Avis Admin';
$pageDescription = 'Gestion des avis QueueLess.';
$currentPage = 'admin_reviews';
$extraCss = ['dashboard.css'];
$extraJs = ['dashboard.js'];

$reviews = [];
$databaseWarning = null;

try {
    $pdo = getPDO();
    $statement = $pdo->query(
        'SELECT
            a.id,
            a.note,
            a.commentaire,
            a.created_at,
            u.prenom,
            u.nom,
            s.nom AS service_nom
        FROM avis a
        INNER JOIN users u ON u.id = a.user_id
        INNER JOIN services s ON s.id = a.service_id
        ORDER BY a.created_at DESC'
    );
    $reviews = $statement->fetchAll();
} catch (Throwable $exception) {
    $databaseWarning = 'Impossible de charger les avis pour le moment.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-section">
    <div class="container dashboard-layout">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-hero">
                <span class="dashboard-kicker">Feedback clients</span>
                <h1>Avis</h1>
                <p>Consultez les notes et commentaires laisses apres reservation.</p>
            </div>

            <?php if ($databaseWarning): ?>
                <div class="alert alert-warning">
                    <span><?= e($databaseWarning); ?></span>
                </div>
            <?php endif; ?>

            <section class="content-panel">
                <div class="panel-heading">
                    <div>
                        <span class="small-label">Commentaires</span>
                        <h2>Liste des avis</h2>
                    </div>
                </div>

                <?php if ($reviews): ?>
                    <div class="activity-list">
                        <?php foreach ($reviews as $review): ?>
                            <article class="review-admin-card">
                                <div class="review-admin-top">
                                    <div>
                                        <strong><?= e((string) $review['service_nom']); ?></strong>
                                        <p><?= e(trim((string) (($review['prenom'] ?? '') . ' ' . ($review['nom'] ?? '')))); ?> - <?= e(format_datetime((string) $review['created_at'], 'd/m/Y')); ?></p>
                                    </div>
                                    <div class="table-actions">
                                        <span class="status-badge warning">Note <?= e((string) $review['note']); ?>/5</span>
                                        <form method="POST" action="<?= url('actions/delete_review_action.php'); ?>">
                                            <input type="hidden" name="review_id" value="<?= e((string) $review['id']); ?>">
                                            <button class="table-button danger" type="submit" data-confirm="Supprimer cet avis ?">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p><?= e((string) ($review['commentaire'] ?: '-')); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>Aucun avis disponible.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
