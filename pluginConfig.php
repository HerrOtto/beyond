<?php

header('Content-type: text/html; Charset=UTF-8');

require_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

$plugin = preg_replace('/[^a-z0-9\-]/', '', $beyond->variable->get('name', ''));

?>
<html>
<head>
    <title><?php print $plugin; ?> plugin</title>
    <?php require_once __DIR__ . '/inc/head.php'; ?>

    <?php
    if (file_exists(__DIR__ . '/plugins/' . $plugin . '/configHead.php')) {
        require_once __DIR__ . '/plugins/' . $plugin . '/configHead.php';
    }
    ?>
</head>
<body class="sb-nav-fixed">
<?php require_once __DIR__ . '/inc/begin.php'; ?>
<?php require_once __DIR__ . '/inc/menuTop.php'; ?>
<div id="layoutSidenav">
    <?php require_once __DIR__ . '/inc/menuSide.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <?php require_once __DIR__ . '/inc/beginSite.php'; ?>

                <ol class="breadcrumb mb-4 mt-4">
                    <li class="breadcrumb-item active">Plugin configuration : <?php print $plugin; ?></li>
                </ol>

                <?php
                if (file_exists(__DIR__ . '/plugins/' . $plugin . '/config.php')) {
                    require_once __DIR__ . '/plugins/' . $plugin . '/config.php';
                }
                ?>

                <?php require_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>