<?php

header('Content-type: text/html; Charset=UTF-8');

require_once __DIR__ . '/inc/init.php';
if (!$tools->checkRole('admin,view')) {
    header('Location: ' . $config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

?>
<html>
<head>
    <title>Plugins</title>


    <style>

        .pluginItem {
            display: table;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 4px;
            cursor: pointer;
        }

        .pluginItem:hover {
            border: 1px solid #c0c0c0;
            background-color: #f0f0f0;
        }

        .pluginItemIcon {
            display: table-cell;
            padding-left: 5px;
            padding-right: 5px;
            width: 0.1%;
        }

        .pluginItemName {
            display: table-cell;
            width: 99.8%;
        }

        .pluginItemAction {
            display: table-cell;
            width: 0.1%;
            padding-left: 5px;
            padding-right: 5px;
        }

    </style>

    <?php require_once __DIR__ . '/inc/head.php'; ?>
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
                    <li class="breadcrumb-item active">Manage plugins</li>
                </ol>

                <div class="mb-4">
                    <?php
                    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
                        if (!is_dir($pluginDir)) {
                            continue;
                        }

                        print '<div class="pluginItem">';
                        print '<span class="pluginItemIcon">';
                        print '<i class="fas fa-puzzle-piece"></i>';
                        print '</span>';
                        print '<span class="pluginItemName">';
                        print basename($pluginDir);
                        print '</span>';
                        print '<span class="pluginItemAction text-nowrap">';
                        print '</span>';
                        print '</div>';
                    }
                    ?>
                </div>

                <?php require_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
