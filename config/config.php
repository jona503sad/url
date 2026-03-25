<?php
define('DB_HOST', 'sql105.infinityfree.com');
define('DB_NAME', 'epiz_29857150_666');
define('DB_USER', 'epiz_29857150');
define('DB_PASS', 'wSGXJt33nH0Rzon');

// Forzar SSL y detectar dominio automáticamente
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}
define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/');
?>