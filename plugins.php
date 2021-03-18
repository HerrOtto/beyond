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
    <title>Plugins</title>

    <style>

        .pluginItem {
            display: table;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 4px;
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
                    <li class="breadcrumb-item active">Manage plugins (click to configure)</li>
                </ol>

                <div class="mb-4">
                    <?php
                    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
                        if (!is_dir($pluginDir)) {
                            continue;
                        }
                        if (file_exists($pluginDir . '/config.php')) {
                            $link = $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginConfig.php?name=' . basename($pluginDir);
                        } else {
                            $link = '';
                        }
                        print '<div class="pluginItem">';
                        print '<span class="pluginItemIcon">';
                        print '<i class="fas fa-puzzle-piece"></i>';
                        print '</span>';
                        if ($link === '') {
                            print '<span class="pluginItemName" style="cursor:not-allowed;">';
                        } else {
                            print '<span class="pluginItemName" style="cursor:pointer;" onclick="location.href=\'' . $link . '\';">';
                        }
                        print basename($pluginDir);
                        print '</span>';
                        print '<span class="pluginItemAction text-nowrap">';
                        print '</span>';
                        print '</div>';
                    }
                    unset($pluginDir);
                    ?>
                </div>

                <?php include_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php include_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
