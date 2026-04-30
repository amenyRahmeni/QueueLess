<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if (!is_post_request()) {
    redirect('pages/profile.php');
}

$user = current_user();
$userId = (int) ($user['id'] ?? 0);

$nom = trim((string) ($_POST['nom'] ?? ''));
$prenom = trim((string) ($_POST['prenom'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$telephone = trim((string) ($_POST['telephone'] ?? ''));

set_old_input([
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
]);

if ($nom === '' || $prenom === '' || $email === '' || $telephone === '') {
    set_flash_message('error', 'Veuillez remplir tous les champs du profil.');
    redirect('pages/profile.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash_message('error', 'Adresse email invalide.');
    redirect('pages/profile.php');
}

try {
    $pdo = getPDO();

    $emailStatement = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
    $emailStatement->execute([
        'email' => $email,
        'id' => $userId,
    ]);

    if ($emailStatement->fetch()) {
        set_flash_message('error', 'Cette adresse email est deja utilisee par un autre compte.');
        redirect('pages/profile.php');
    }

    $updateStatement = $pdo->prepare(
        'UPDATE users
        SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone
        WHERE id = :id'
    );

    $updateStatement->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'telephone' => $telephone,
        'id' => $userId,
    ]);

    $userStatement = $pdo->prepare(
        'SELECT id, nom, prenom, email, telephone, role, created_at
        FROM users
        WHERE id = :id
        LIMIT 1'
    );
    $userStatement->execute([
        'id' => $userId,
    ]);

    $updatedUser = $userStatement->fetch();

    if ($updatedUser) {
        $_SESSION['user'] = $updatedUser;
    }

    clear_old_input();
    set_flash_message('success', 'Profil mis a jour avec succes.');
} catch (Throwable $exception) {
    set_flash_message('error', 'Impossible de mettre a jour le profil pour le moment.');
}

redirect('pages/profile.php');

