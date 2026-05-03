<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/owner_check.php';

if (!is_post_request()) {
    redirect('owner/slots.php');
}


$slotId = (int) ($_POST['slot_id'] ?? 0);
$serviceId = (int) ($_POST['service_id'] ?? 0);
$dateSlot = trim((string) ($_POST['date_slot'] ?? ''));
$heureDebut = trim((string) ($_POST['heure_debut'] ?? ''));
$heureFin = trim((string) ($_POST['heure_fin'] ?? ''));
$disponible = isset($_POST['disponible']) ? 1 : 0;

if ($slotId <= 0 || $serviceId <= 0 || $dateSlot === '' || $heureDebut === '' || $heureFin === '') {
    set_flash_message('error', 'Veuillez remplir tous les champs du creneau.');
    redirect('owner/edit_slot.php?id=' . $slotId);
}

if ($heureFin <= $heureDebut) {
    set_flash_message('error', 'L heure de fin doit etre superieure a l heure de debut.');
    redirect('owner/edit_slot.php?id=' . $slotId);
}

try {
    $pdo = getPDO();

    if (!current_user_owns_service($pdo, $serviceId)) {
        set_flash_message('error', 'Vous ne pouvez modifier que les creneaux de vos services.');
        redirect('owner/edit_slot.php?id=' . $slotId);
    }

    $slotStatement = $pdo->prepare(
        'SELECT COUNT(*)
        FROM slots sl
        INNER JOIN services s ON s.id = sl.service_id
        WHERE sl.id = :id AND s.owner_id = :owner_id'
    );
    $slotStatement->execute([
        'id' => $slotId,
        'owner_id' => (int) (current_user()['id'] ?? 0),
    ]);
    if ((int) $slotStatement->fetchColumn() === 0) {
        set_flash_message('error', 'Creneau introuvable.');
        redirect('owner/slots.php');
    }

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
    redirect('owner/slots.php');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de mettre a jour ce creneau.');
    redirect('owner/edit_slot.php?id=' . $slotId);
}
