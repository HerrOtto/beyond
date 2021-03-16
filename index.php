<?php

header('Content-type: text/html; Charset=UTF-8');

include_once __DIR__ . '/inc/init.php';

if ($beyond->tools->currentUser() === false) {
    // Not logged in?
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
} else if ($beyond->tools->checkRole('admin,view')) {
    // Is admin or viewer
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php');
    exit;
} else if ($beyond->config->get('base', 'backend.allowOtherRoles', false) == true) {
    // Is unknown role but configured
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/welcome.php');
    exit;
} else {
    // Otherwise
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
}


