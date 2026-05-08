<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    set_flash_message('warning', 'Veuillez vous connecter pour acceder a cette page.');
    redirect('pages/login.php');
}

