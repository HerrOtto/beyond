<?php

header('Content-type: text/html; Charset=UTF-8');
include_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    // Is not admin or viewer
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

?>
<html>
<head>
    <title>Config</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>
</head>
<body class="sb-nav-fixed">
<?php include_once __DIR__ . '/inc/begin.php'; ?>
<?php include_once __DIR__ . '/inc/menuTop.php'; ?>
<div id="layoutSidenav">
    <?php include_once __DIR__ . '/inc/menuSide.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <?php include_once __DIR__ . '/inc/beginSite.php'; ?>

                <ol class="breadcrumb mb-4 mt-4">
                    <li class="breadcrumb-item active">Config</li>
                </ol>

                TODO: edit config files by json<br>

                <?php include_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php include_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
