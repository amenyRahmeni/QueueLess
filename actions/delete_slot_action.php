<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/owner_check.php';

if (!is_post_request()) {
    redirect('owner/slots.php');
}

$slotId = (int) ($_POST['slot_id'] ?? 0);

if ($slotId <= 0) {
    set_flash_message('error', 'Creneau invalide.');
    redirect('owner/slots.php');
}

try {
    $pdo = getPDO();

    $statement = $pdo->prepare(
        'DELETE sl
        FROM slots sl
        INNER JOIN services s ON s.id = sl.service_id
        WHERE sl.id = :id AND s.owner_id = :owner_id'
    );
    $statement->execute([
        'id' => $slotId,
        'owner_id' => (int) (current_user()['id'] ?? 0),
    ]);

    if ($statement->rowCount() > 0) {
        set_flash_message('success', 'Le creneau a ete supprime avec succes.');
    } else {
        set_flash_message('error', 'Creneau introuvable.');
    }
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de supprimer ce creneau. Verifiez les reservations liees.');
}

redirect('owner/slots.php');
