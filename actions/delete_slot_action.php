<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

if (!is_post_request()) {
    redirect('admin/slots.php');
}

$slotId = (int) ($_POST['slot_id'] ?? 0);

if ($slotId <= 0) {
    set_flash_message('error', 'Creneau invalide.');
    redirect('admin/slots.php');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare('DELETE FROM slots WHERE id = :id');
    $statement->execute([
        'id' => $slotId,
    ]);

    set_flash_message('success', 'Le creneau a ete supprime avec succes.');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de supprimer ce creneau. Verifiez les reservations liees.');
}

redirect('admin/slots.php');

