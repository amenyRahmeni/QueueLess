<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if (!is_post_request()) {
    redirect('pages/my_reservations.php');
}

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$serviceId = (int) ($_POST['service_id'] ?? 0);
$note = (int) ($_POST['note'] ?? 0);
$commentaire = trim((string) ($_POST['commentaire'] ?? ''));

set_old_input([
    'note' => (string) $note,
    'commentaire' => $commentaire,
]);


if ($reservationId <= 0 || $serviceId <= 0) {
    set_flash_message('error', 'Informations d avis invalides.');
    redirect('pages/my_reservations.php');
}

if ($note < 1 || $note > 5) {
    set_flash_message('error', 'La note doit etre comprise entre 1 et 5.');
    redirect('pages/add_review.php?reservation_id=' . $reservationId);
}

if ($commentaire === '') {
    set_flash_message('error', 'Veuillez ajouter un commentaire pour votre avis.');
    redirect('pages/add_review.php?reservation_id=' . $reservationId);
}


try {
    $pdo = getPDO();

    $reservationStatement = $pdo->prepare(
        'SELECT
            r.id,
            r.service_id,
            CASE
                WHEN r.statut IN ("reservee", "terminee") AND TIMESTAMP(sl.date_slot, sl.heure_fin) <= NOW() THEN 1
                ELSE 0
            END AS can_review
        FROM reservations r
        INNER JOIN slots sl ON sl.id = r.slot_id
        WHERE r.id = :reservation_id
            AND r.user_id = :user_id
            AND r.service_id = :service_id
        LIMIT 1'
    );
    $reservationStatement->execute([
        'reservation_id' => $reservationId,
        'user_id' => $userId,
        'service_id' => $serviceId,
    ]);

    $reservation = $reservationStatement->fetch();

    if (!$reservation || (int) ($reservation['can_review'] ?? 0) !== 1) {
        set_flash_message('error', 'Vous ne pouvez pas laisser un avis pour cette reservation.');
        redirect('pages/my_reservations.php');
    }

    $reviewStatement = $pdo->prepare(
        'INSERT INTO avis (user_id, service_id, note, commentaire)
        VALUES (:user_id, :service_id, :note, :commentaire)
        ON DUPLICATE KEY UPDATE
            note = VALUES(note),
            commentaire = VALUES(commentaire)'
    );
    
    $reviewStatement->execute([
        'user_id' => $userId,
        'service_id' => $serviceId,
        'note' => $note,
        'commentaire' => $commentaire,
    ]);

    clear_old_input();
    set_flash_message('success', 'Votre avis a ete enregistre avec succes.');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible d enregistrer votre avis pour le moment.');
    redirect('pages/add_review.php?reservation_id=' . $reservationId);
}


redirect('pages/my_reservations.php');
