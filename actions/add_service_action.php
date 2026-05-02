<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if (!is_admin() && !is_service_owner()) {
    set_flash_message('error', 'Acces refuse.');
    redirect('index.php');
}

$isOwner = is_service_owner();
$formPath = $isOwner ? 'owner/add_service.php' : 'admin/add_service.php';
$successPath = $isOwner ? 'owner/services.php' : 'admin/services.php';

if (!is_post_request()) {
    redirect($formPath);
}


$nom = trim((string) ($_POST['nom'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$categorie = trim((string) ($_POST['categorie'] ?? ''));
$adresse = trim((string) ($_POST['adresse'] ?? ''));
$ownerId = $isOwner ? (int) (current_user()['id'] ?? 0) : (int) ($_POST['owner_id'] ?? 0);
$dureeMoyenne = (int) ($_POST['duree_moyenne'] ?? 0);
$actif = isset($_POST['actif']) ? 1 : 0;


set_old_input([
    'nom' => $nom,
    'description' => $description,
    'categorie' => $categorie,
    'adresse' => $adresse,
    'owner_id' => (string) $ownerId,
    'duree_moyenne' => (string) $dureeMoyenne,
    'actif' => $actif ? '1' : '',
]);

if ($nom === '' || $description === '' || $categorie === '' || $adresse === '' || $dureeMoyenne <= 0) {
    set_flash_message('error', 'Veuillez remplir tous les champs obligatoires du service.');
    redirect($formPath);
}

try {
    $imageName = upload_service_image($_FILES['image'] ?? []);

    $pdo = getPDO();
    if ($ownerId > 0) {
        $ownerStatement = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id = :id AND role = "owner"');
        $ownerStatement->execute(['id' => $ownerId]);
        if ((int) $ownerStatement->fetchColumn() === 0) {
            set_flash_message('error', 'Proprietaire du service invalide.');
            redirect($formPath);
        }
    }

    $statement = $pdo->prepare(
        'INSERT INTO services (owner_id, nom, description, categorie, adresse, image, duree_moyenne, actif)
        VALUES (:owner_id, :nom, :description, :categorie, :adresse, :image, :duree_moyenne, :actif)'
    );
    $statement->execute([
        'owner_id' => $ownerId > 0 ? $ownerId : null,
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
    redirect($successPath);
} catch (Throwable $exception) {
    set_flash_message('error', $exception->getMessage());
    redirect($formPath);
}

