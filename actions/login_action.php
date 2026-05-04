<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (!is_post_request()) {
    redirect('pages/login.php');
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

set_old_input([
    'email' => $email,
]);

if ($email === '' || $password === '') {
    set_flash_message('error', 'Veuillez remplir tous les champs.');
    redirect('pages/login.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash_message('error', 'Adresse email invalide.');
    redirect('pages/login.php');
}


$pdo = getPDO();
$statement = $pdo->prepare('SELECT id, nom, prenom, email, password, telephone, role, created_at FROM users WHERE email = :email LIMIT 1');
$statement->execute([
    'email' => $email,
]);


$user = $statement->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    set_flash_message('error', 'Email ou mot de passe incorrect.');
    redirect('pages/login.php');
}

unset($user['password']);
session_regenerate_id(true);
$_SESSION['user'] = $user;

clear_old_input();
set_flash_message('success', 'Connexion reussie. Bienvenue sur QueueLess.');
redirect(user_dashboard_path());
