<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (!is_post_request()) {
    redirect('pages/register.php');
}


$nom = trim((string) ($_POST['nom'] ?? ''));
$prenom = trim((string) ($_POST['prenom'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$telephone = trim((string) ($_POST['telephone'] ?? ''));
$accountType = (string) ($_POST['account_type'] ?? 'user');
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$allowedAccountTypes = ['admin', 'user', 'owner'];

if (!in_array($accountType, $allowedAccountTypes, true)) {
    $accountType = 'user';
}

$role = $accountType;

set_old_input([
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
    'account_type' => $role,
]);

if ($nom === '' || $prenom === '' || $email === '' || $telephone === '' || $password === '' || $confirmPassword === '') {
    set_flash_message('error', 'Veuillez remplir tous les champs.');
    redirect('pages/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash_message('error', 'Adresse email invalide.');
    redirect('pages/register.php');
}

if (strlen($password) < 8) {
    set_flash_message('error', 'Le mot de passe doit contenir au moins 8 caracteres.');
    redirect('pages/register.php');
}

if ($password !== $confirmPassword) {
    set_flash_message('error', 'Les mots de passe ne correspondent pas.');
    redirect('pages/register.php');
}

$pdo = getPDO();
$checkStatement = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$checkStatement->execute([
    'email' => $email,
]);

if ($checkStatement->fetch()) {
    set_flash_message('error', 'Cette adresse email est deja utilisee.');
    redirect('pages/register.php');
}

$insertStatement = $pdo->prepare(
    'INSERT INTO users (nom, prenom, email, password, telephone, role) VALUES (:nom, :prenom, :email, :password, :telephone, :role)'
);

$insertStatement->execute([
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'telephone' => $telephone,
    'role' => $role,
]);

$userId = (int) $pdo->lastInsertId();
$userStatement = $pdo->prepare('SELECT id, nom, prenom, email, telephone, role, created_at FROM users WHERE id = :id LIMIT 1');
$userStatement->execute([
    'id' => $userId,
]);

$user = $userStatement->fetch();

if ($user) {
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
}


clear_old_input();
set_flash_message('success', 'Compte cree avec succes. Bienvenue sur QueueLess.');
redirect(user_dashboard_path());
