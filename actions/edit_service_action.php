<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

if (!is_post_request()) {
    redirect('admin/services.php');
}

$serviceId = (int) ($_POST['service_id'] ?? 0);
$nom = trim((string) ($_POST['nom'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$categorie = trim((string) ($_POST['categorie'] ?? ''));
$adresse = trim((string) ($_POST['adresse'] ?? ''));
$dureeMoyenne = (int) ($_POST['duree_moyenne'] ?? 0);
$actif = isset($_POST['actif']) ? 1 : 0;

if ($serviceId <= 0) {
    set_flash_message('error', 'Service invalide.');
    redirect('admin/services.php');
}

if ($nom === '' || $description === '' || $categorie === '' || $adresse === '' || $dureeMoyenne <= 0) {
    set_flash_message('error', 'Veuillez remplir tous les champs obligatoires du service.');
    redirect('admin/edit_service.php?id=' . $serviceId);
}

try {
    $pdo = getPDO();

    $serviceStatement = $pdo->prepare('SELECT image FROM services WHERE id = :id LIMIT 1');
    $serviceStatement->execute([
        'id' => $serviceId,
    ]);
    $service = $serviceStatement->fetch();

    if (!$service) {
        set_flash_message('error', 'Service introuvable.');
        redirect('admin/services.php');
    }

    $imageName = (string) ($service['image'] ?? '');

    if (!empty($_FILES['image']['name'])) {
        $newImageName = upload_service_image($_FILES['image']);
        if ($newImageName !== null) {
            $imageName = $newImageName;
        }
    }

    $updateStatement = $pdo->prepare(
        'UPDATE services
        SET nom = :nom,
            description = :description,
            categorie = :categorie,
            adresse = :adresse,
            image = :image,
            duree_moyenne = :duree_moyenne,
            actif = :actif
        WHERE id = :id'
    );
    $updateStatement->execute([
        'nom' => $nom,
        'description' => $description,
        'categorie' => $categorie,
        'adresse' => $adresse,
        'image' => $imageName !== '' ? $imageName : null,
        'duree_moyenne' => $dureeMoyenne,
        'actif' => $actif,
        'id' => $serviceId,
    ]);

    set_flash_message('success', 'Le service a ete mis a jour avec succes.');
    redirect('admin/services.php');
} catch (Throwable $exception) {
    set_flash_message('error', $exception->getMessage());
    redirect('admin/edit_service.php?id=' . $serviceId);
}

