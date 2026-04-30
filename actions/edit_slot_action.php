<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

if (!is_post_request()) {
    redirect('admin/slots.php');
}

$slotId = (int) ($_POST['slot_id'] ?? 0);
$serviceId = (int) ($_POST['service_id'] ?? 0);
$dateSlot = trim((string) ($_POST['date_slot'] ?? ''));
$heureDebut = trim((string) ($_POST['heure_debut'] ?? ''));
$heureFin = trim((string) ($_POST['heure_fin'] ?? ''));
$disponible = isset($_POST['disponible']) ? 1 : 0;

if ($slotId <= 0 || $serviceId <= 0 || $dateSlot === '' || $heureDebut === '' || $heureFin === '') {
    set_flash_message('error', 'Veuillez remplir tous les champs du creneau.');
    redirect('admin/edit_slot.php?id=' . $slotId);
}

if ($heureFin <= $heureDebut) {
    set_flash_message('error', 'L heure de fin doit etre superieure a l heure de debut.');
    redirect('admin/edit_slot.php?id=' . $slotId);
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'UPDATE slots
        SET service_id = :service_id,
            date_slot = :date_slot,
            heure_debut = :heure_debut,
            heure_fin = :heure_fin,
            disponible = :disponible
        WHERE id = :id'
    );
    $statement->execute([
        'service_id' => $serviceId,
        'date_slot' => $dateSlot,
        'heure_debut' => $heureDebut,
        'heure_fin' => $heureFin,
        'disponible' => $disponible,
        'id' => $slotId,
    ]);

    set_flash_message('success', 'Le creneau a ete mis a jour avec succes.');
    redirect('admin/slots.php');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de mettre a jour ce creneau.');
    redirect('admin/edit_slot.php?id=' . $slotId);
}

