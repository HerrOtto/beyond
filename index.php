<?php

header('Content-type: text/html; Charset=UTF-8');

require_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

// Otherwise jump to admin/beyond panel
header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php');

