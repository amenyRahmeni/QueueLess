<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if (!is_post_request()) {
    redirect('pages/my_reservations.php');
}

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$reservationId = (int) ($_POST['reservation_id'] ?? 0);

if ($reservationId <= 0) {
    set_flash_message('error', 'Reservation invalide.');
    redirect('pages/my_reservations.php');
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $reservationStatement = $pdo->prepare(
        'SELECT
            r.id,
            r.statut,
            r.slot_id,
            r.service_id,
            sl.date_slot
        FROM reservations r
        INNER JOIN slots sl ON sl.id = r.slot_id
        WHERE r.id = :reservation_id
            AND r.user_id = :user_id
        FOR UPDATE'
    );
    $reservationStatement->execute([
        'reservation_id' => $reservationId,
        'user_id' => $userId,
    ]);

    $reservation = $reservationStatement->fetch();

    if (!$reservation) {
        $pdo->rollBack();
        set_flash_message('error', 'Reservation introuvable.');
        redirect('pages/my_reservations.php');
    }

    if ((string) $reservation['statut'] !== 'reservee') {
        $pdo->rollBack();
        set_flash_message('warning', 'Cette reservation ne peut plus etre annulee.');
        redirect('pages/my_reservations.php');
    }

    $updateReservationStatement = $pdo->prepare(
        'UPDATE reservations
        SET statut = :statut
        WHERE id = :id'
    );
    $updateReservationStatement->execute([
        'statut' => 'annulee',
        'id' => $reservationId,
    ]);

    $updateSlotStatement = $pdo->prepare('UPDATE slots SET disponible = 1 WHERE id = :slot_id');
    $updateSlotStatement->execute([
        'slot_id' => (int) $reservation['slot_id'],
    ]);

    $pdo->commit();

    set_flash_message('success', 'La reservation a ete annulee et le creneau est de nouveau disponible.');
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash_message('error', 'Impossible d annuler la reservation pour le moment.');
}

redirect('pages/my_reservations.php');

