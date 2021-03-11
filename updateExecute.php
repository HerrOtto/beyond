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

                <div class="box m-4 mt-5">

                    <?php
                    try {

                        // Check current version on github
                        print "<div class='status'>Checking current version</div>";
                        $githubUrl = 'https://raw.githubusercontent.com/HerrOtto/beyond/master/version.json?nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10)));
                        print '<div class="note">URL: ' . $githubUrl . '</div>';
                        $versionFromGithub = $tools->httpGet($githubUrl);
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

                        //
                        print "<div class='status'>Download current version</div>";
                        $zipUrl = 'https://codeload.github.com/HerrOtto/beyond/zip/master?nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10)));
                        $zipFile = __DIR__ . '/temp/update.zip';
                        $zipResult = $tools->httpGet($zipUrl, $zipFile, 60);
                        if ($zipResult !== true) {
                            throw new Exception('Cannot retrieve update from GitHub [https://codeload.github.com/HerrOtto/beyond/zip/master]');
                        }
                        print '<div class="note">Download done</div>';

                        // Extract ZIP archive
                        print "<div class='status'>Extracting files!</div>";
                        $error = false;
                        $za = new ZipArchive();
                        if ($za->open($zipFile) !== true) {
                            throw new Exception('Error processing ZIP archive [' . $zipFile . ']');
                        } else {
                            for ($i = 0; $i < $za->numFiles; $i++) {
                                $stat = $za->statIndex($i);

                                if (preg_match('/^beyond\-master\/config\//', $stat['name'])) {
                                    print '<div class="note">IGNORE: ' . $stat['name'] . '</div>';
                                } else if (preg_match('/^beyond\-master(\/.*)/', $stat['name'], $matches)) {

                                    // Extract
                                    if ($data = $za->getFromName($stat['name'])) {
                                        if (file_put_contents(__DIR__ . $matches[1], $data) === false) {
                                            print '<div class="note">ERROR: ' . $stat['name'] . ' (' . $matches[1] . ')</div>';
                                            $error = true;
                                        } else {
                                            print '<div class="note">UPDATE: ' . $stat['name'] . ' (' . $matches[1] . ')</div>';
                                        }
                                    }

                                } else {
                                    print '<div class="note">ERROR: ' . $stat['name'] . '</div>';
                                }
                            }
                        }
                        if ($error === true) {
                            throw new Exception('Not all files were written successfully');
                        }

                        print "<div class='status'>Done!</div>";

                    } catch (Exception $e) {
                        print '<div class="error">' . $e->getMessage() . '</div>';
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
