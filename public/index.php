<?php

use CodeIgniter\Boot;
use Config\Paths;

// Railway terminates HTTPS at its edge and forwards plain HTTP to Apache.
// Its edge overwrites X-Forwarded-Proto with the original client scheme.
// Normalize the server flag before CodeIgniter snapshots request globals so
// production Secure cookies work without trusting forwarded headers locally.
if (getenv('RAILWAY_PROJECT_ID') !== false && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';
exit(Boot::bootWeb($paths));
