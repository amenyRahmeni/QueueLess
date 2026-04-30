<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

if (!is_post_request()) {
    redirect('admin/reviews.php');
}

$reviewId = (int) ($_POST['review_id'] ?? 0);

if ($reviewId <= 0) {
    set_flash_message('error', 'Avis invalide.');
    redirect('admin/reviews.php');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare('DELETE FROM avis WHERE id = :id');
    $statement->execute([
        'id' => $reviewId,
    ]);

    set_flash_message('success', 'L avis a ete supprime avec succes.');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de supprimer cet avis pour le moment.');
}

redirect('admin/reviews.php');
