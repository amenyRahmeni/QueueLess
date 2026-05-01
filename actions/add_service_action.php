<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_check.php';

if (!is_post_request()) {
    redirect('admin/add_service.php');
}


$nom = trim((string) ($_POST['nom'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$categorie = trim((string) ($_POST['categorie'] ?? ''));
$adresse = trim((string) ($_POST['adresse'] ?? ''));
$dureeMoyenne = (int) ($_POST['duree_moyenne'] ?? 0);
$actif = isset($_POST['actif']) ? 1 : 0;


set_old_input([
    'nom' => $nom,
    'description' => $description,
    'categorie' => $categorie,
    'adresse' => $adresse,
    'duree_moyenne' => (string) $dureeMoyenne,
    'actif' => $actif ? '1' : '',
]);

if ($nom === '' || $description === '' || $categorie === '' || $adresse === '' || $dureeMoyenne <= 0) {
    set_flash_message('error', 'Veuillez remplir tous les champs obligatoires du service.');
    redirect('admin/add_service.php');
}

try {
    $imageName = upload_service_image($_FILES['image'] ?? []);

    $pdo = getPDO();
    $statement = $pdo->prepare(
        'INSERT INTO services (nom, description, categorie, adresse, image, duree_moyenne, actif)
        VALUES (:nom, :description, :categorie, :adresse, :image, :duree_moyenne, :actif)'
    );
    $statement->execute([
        'nom' => $nom,
        'description' => $description,
        'categorie' => $categorie,
        'adresse' => $adresse,
        'image' => $imageName,
        'duree_moyenne' => $dureeMoyenne,
        'actif' => $actif,
    ]);

    clear_old_input();
    set_flash_message('success', 'Le service a ete ajoute avec succes.');
    redirect('admin/services.php');
} catch (Throwable $exception) {
    set_flash_message('error', $exception->getMessage());
    redirect('admin/add_service.php');
}

