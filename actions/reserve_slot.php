<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if (!is_post_request()) {
    redirect('pages/services.php');
}

$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$serviceId = (int) ($_POST['service_id'] ?? 0);
$slotId = (int) ($_POST['slot_id'] ?? 0);
$commentaire = trim((string) ($_POST['commentaire'] ?? ''));

if ($serviceId <= 0 || $slotId <= 0) {
    set_flash_message('error', 'Informations de reservation invalides.');
    redirect('pages/services.php');
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $slotStatement = $pdo->prepare(
        'SELECT
            sl.id,
            sl.service_id,
            sl.date_slot,
            sl.heure_debut,
            sl.heure_fin,
            sl.disponible,
            s.nom AS service_nom,
            s.actif
        FROM slots sl
        INNER JOIN services s ON s.id = sl.service_id
        WHERE sl.id = :slot_id
            AND sl.service_id = :service_id
        FOR UPDATE'
    );
    $slotStatement->execute([
        'slot_id' => $slotId,
        'service_id' => $serviceId,
    ]);

    $slot = $slotStatement->fetch();

    if (!$slot) {
        $pdo->rollBack();
        set_flash_message('error', 'Le creneau demande est introuvable.');
        redirect('pages/services.php');
    }

    if ((int) $slot['actif'] !== 1) {
        $pdo->rollBack();
        set_flash_message('error', 'Ce service n est pas disponible actuellement.');
        redirect('pages/service_details.php?id=' . $serviceId);
    }

    if ((int) $slot['disponible'] !== 1) {
        $pdo->rollBack();
        set_flash_message('error', 'Ce creneau vient d etre reserve. Veuillez en choisir un autre.');
        redirect('pages/service_details.php?id=' . $serviceId);
    }

    $insertStatement = $pdo->prepare(
        'INSERT INTO reservations (user_id, service_id, slot_id, statut, commentaire)
        VALUES (:user_id, :service_id, :slot_id, :statut, :commentaire)'
    );
    $insertStatement->execute([
        'user_id' => $userId,
        'service_id' => $serviceId,
        'slot_id' => $slotId,
        'statut' => 'reservee',
        'commentaire' => $commentaire !== '' ? $commentaire : null,
    ]);

    $updateSlotStatement = $pdo->prepare('UPDATE slots SET disponible = 0 WHERE id = :id');
    $updateSlotStatement->execute([
        'id' => $slotId,
    ]);

    $pdo->commit();

    set_flash_message('success', 'Votre reservation a ete confirmee avec succes.');
    redirect('pages/my_reservations.php');
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash_message('error', 'Impossible d enregistrer la reservation pour le moment.');
    redirect('pages/service_details.php?id=' . $serviceId);
}

