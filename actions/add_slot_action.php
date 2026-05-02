<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/owner_check.php';

if (!is_post_request()) {
    redirect('owner/add_slot.php');
}


$serviceId = (int) ($_POST['service_id'] ?? 0);
$dateSlot = trim((string) ($_POST['date_slot'] ?? ''));
$heureDebut = trim((string) ($_POST['heure_debut'] ?? ''));
$heureFin = trim((string) ($_POST['heure_fin'] ?? ''));
$disponible = isset($_POST['disponible']) ? 1 : 0;

if ($serviceId <= 0 || $dateSlot === '' || $heureDebut === '' || $heureFin === '') {
    set_flash_message('error', 'Veuillez remplir tous les champs du creneau.');
    redirect('owner/add_slot.php');
}

if ($heureFin <= $heureDebut) {
    set_flash_message('error', 'L heure de fin doit etre superieure a l heure de debut.');
    redirect('owner/add_slot.php');
}

try {
    $pdo = getPDO();

    if (!current_user_owns_service($pdo, $serviceId)) {
        set_flash_message('error', 'Vous ne pouvez ajouter des creneaux que pour vos services.');
        redirect('owner/add_slot.php');
    }

    $statement = $pdo->prepare(
        'INSERT INTO slots (service_id, date_slot, heure_debut, heure_fin, disponible)
        VALUES (:service_id, :date_slot, :heure_debut, :heure_fin, :disponible)'
    );
    $statement->execute([
        'service_id' => $serviceId,
        'date_slot' => $dateSlot,
        'heure_debut' => $heureDebut,
        'heure_fin' => $heureFin,
        'disponible' => $disponible,
    ]);

    set_flash_message('success', 'Le creneau a ete ajoute avec succes.');
    redirect('owner/slots.php');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible d ajouter ce creneau. Verifiez les donnees saisies.');
    redirect('owner/add_slot.php');
}
