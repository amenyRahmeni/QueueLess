<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';

if (!is_admin()) {
    set_flash_message('error', 'Acces refuse. Cette page est reservee a l administrateur.');
    redirect('index.php');
}

