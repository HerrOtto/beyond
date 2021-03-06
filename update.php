<?php

header('Content-type: text/html; Charset=UTF-8');

include_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin')) {
    // Is not admin or viewer
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

?>
<html>
<head>
    <title>Update</title>

    <style>
        .status {
            font-weight: bold;
            margin-bottom: 12px;
        }

        .note {
            font-weight: lighter;
            margin-bottom: 12px;
        }

        .error {
            font-weight: lighter;
            margin-bottom: 12px;
            color: #ff0000;
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
                    <li class="breadcrumb-item active">Update</li>
                </ol>

                <div class="box m-4 mt-5">
                    <?php
                    try {

                        // Check current version on github
                        print "<div class='status'>Checking current version</div>";
                        $githubUrl = 'https://raw.githubusercontent.com/HerrOtto/beyond/master/version.json?nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10)));
                        print '<div class="note">URL: ' . $githubUrl . '</div>';
                        $versionFromGithub = $beyond->tools->httpGet($githubUrl);
                        if ($versionFromGithub == '') {
                            throw new Exception('Cannot retrieve version information from GitHub [https://raw.githubusercontent.com/HerrOtto/beyond/master/version.json]');
                        }
                        print '<div class="note">Result: ' . $versionFromGithub . '</div>';
                        $versionFromGithubJson = json_decode($versionFromGithub);
                        print '<div class="note">Version on GitHub: ' . $versionFromGithubJson->version . '</div>';

                        // Check current installed version
                        print "<div class='status'>Checking installed version</div>";
                        if (!file_exists(__DIR__ . '/version.json')) {
                            $currentVersion = -1;
                        } else {
                            $currentVersion = trim(file_get_contents(__DIR__ . '/version.json'));
                            if ($currentVersion == '') {
                                throw new Exception('Cannot retrieve current version information');
                            }
                            $currentVersionJson = json_decode($currentVersion);
                            $currentVersion = $currentVersionJson->version;
                        }
                        print '<div class="note">Installed version: ' . $currentVersion . '</div>';
                        if ($currentVersion >= $versionFromGithubJson->version) {
                            throw new Exception('Current version [' . $currentVersion . '] is newer or equal to Github version [' . $versionFromGithubJson->version . ']');
                        }

                        print '<div class="note">Update ready</div>';
                        print '<button onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/updateExecute.php\';">Install update now!</button>';

                    } catch (Exception $e) {
                        print '<div class="error">' . $e->getMessage() . '</div>';
                    }
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
