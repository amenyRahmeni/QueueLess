<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

if (!is_post_request()) {
    redirect('admin/services.php');
}

$serviceId = (int) ($_POST['service_id'] ?? 0);

if ($serviceId <= 0) {
    set_flash_message('error', 'Service invalide.');
    redirect('admin/services.php');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare('DELETE FROM services WHERE id = :id');
    $statement->execute([
        'id' => $serviceId,
    ]);

    set_flash_message('success', 'Le service a ete supprime avec succes.');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de supprimer ce service. Verifiez les reservations liees.');
}

redirect('admin/services.php');

