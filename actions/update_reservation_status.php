<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if (!is_service_owner()) {
    set_flash_message('error', 'Acces refuse.');
    redirect('index.php');
}

$redirectPath = 'owner/reservations.php';

if (!is_post_request()) {
    redirect($redirectPath);
}

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$newStatus = trim((string) ($_POST['statut'] ?? ''));
$allowedStatuses = ['reservee', 'annulee', 'terminee'];

if ($reservationId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
    set_flash_message('error', 'Mise a jour de reservation invalide.');
    redirect($redirectPath);
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $reservationStatement = $pdo->prepare(
        'SELECT r.id, r.statut, r.slot_id, sl.disponible, s.owner_id
        FROM reservations r
        INNER JOIN slots sl ON sl.id = r.slot_id
        INNER JOIN services s ON s.id = r.service_id
        WHERE r.id = :id
        FOR UPDATE'
    );
    $reservationStatement->execute([
        'id' => $reservationId,
    ]);

    $reservation = $reservationStatement->fetch();

    if (!$reservation) {
        $pdo->rollBack();
        set_flash_message('error', 'Reservation introuvable.');
        redirect($redirectPath);
    }

    if ((int) $reservation['owner_id'] !== (int) (current_user()['id'] ?? 0)) {
        $pdo->rollBack();
        set_flash_message('error', 'Vous ne pouvez modifier que les reservations de vos services.');
        redirect($redirectPath);
    }

    $currentStatus = (string) $reservation['statut'];
    $slotId = (int) $reservation['slot_id'];
    $slotAvailable = (int) $reservation['disponible'] === 1;

    if ($newStatus === 'reservee' && $currentStatus !== 'reservee' && !$slotAvailable) {
        $pdo->rollBack();
        set_flash_message('error', 'Ce creneau est deja occupe. Impossible de repasser la reservation a reservee.');
        redirect($redirectPath);
    }

    $updateReservationStatement = $pdo->prepare(
        'UPDATE reservations
        SET statut = :statut
        WHERE id = :id'
    );
    $updateReservationStatement->execute([
        'statut' => $newStatus,
        'id' => $reservationId,
    ]);

    $slotStatus = $newStatus === 'annulee' ? 1 : 0;

    $updateSlotStatement = $pdo->prepare(
        'UPDATE slots
        SET disponible = :disponible
        WHERE id = :id'
    );
    $updateSlotStatement->execute([
        'disponible' => $slotStatus,
        'id' => $slotId,
    ]);

    $pdo->commit();
    set_flash_message('success', 'Le statut de la reservation a ete mis a jour.');
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash_message('error', 'Impossible de mettre a jour cette reservation pour le moment.');
}

redirect($redirectPath);
