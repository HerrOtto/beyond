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
    <title>Update</title>
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
                    <li class="breadcrumb-item active">Update</li>
                </ol>

                <div class="box mb-4">

                    <?php
                    try {

                        // Check current version on github
                        $versionFromGithub = trim(file_get_contents('https://raw.githubusercontent.com/HerrOtto/beyond/master/version.json'));
                        if ($versionFromGithub == '') {
                            throw new Exception('Cannot retrieve version information from GitHub');
                        }
                        $versionFromGithubJson = json_decode($versionFromGithub);

                        // Check current installed version
                        if (!file_exists(__DIR__ . '/config/version.json')) {
                            $currentVersion = 1;
                        } else {
                            $currentVersion = $config->get('version', 'current', 1);
                        }
                        if ($currentVersion >= $versionFromGithubJson->version) {
                            print_r($versionFromGithubJson);
                            throw new Exception('Current version [' . $currentVersion . '] is up to date. (Github version: ' . $versionFromGithubJson->version . ')');
                        }

                        //
                        if ($versionFromGithub == '') {
                            throw new Exception('Cannot retrieve version information from GitHub');
                        }


                    } catch (Exception $e) {
print 'Exception: ' . $e->getMessage();
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
