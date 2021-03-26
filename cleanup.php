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
    <title>Cleanup</title>
    <style>
        .cleanWrapper {
            margin-bottom: 40px;
        }

        .cleanTitle {
            margin-bottom: 10px;
        }

        .cleanItem {
            color: blue;
        }

        .cleanNotFound {
            color: green;
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
                    <li class="breadcrumb-item active">Cleanup</li>
                </ol>

                <div class="box m-4 mt-5">

                    <?php

                    // Get all files
                    $files = array();
                    $dirBase = $beyond->config->get('base', 'server.absPath');

                    print "<div class='cleanWrapper'>";
                    print "<div class='cleanTitle'>";
                    print "<strong>Sanning files</strong>";
                    print "</div>";
                    $found = false;
                    $iteratorDirectory = new RecursiveDirectoryIterator($dirBase);
                    $iterator = new RecursiveIteratorIterator($iteratorDirectory);
                    foreach ($iterator as $iteratorItem) {
                        $file = $iteratorItem->getPathname();
                        if (!is_file($file)) continue;
                        if (substr($file, 0, strlen($dirBase)) !== $dirBase) continue;
                        $relPathAndFile = ltrim(substr($file, strlen($dirBase)), '/');
                        $fileName = basename($relPathAndFile);
                        $relPath = rtrim(substr($relPathAndFile, 0, -strlen($fileName)), '/');
                        if (preg_match('/^beyond\//', $relPath)) continue;
                        $hash = sha1(trim(trim($relPath, '/') . '/' . $fileName, '/'));
                        $files[$hash] = array(
                            'absPath' => $file,
                            'relPath' => $relPath,
                            'fileName' => $fileName,
                            'relPathAndFile' => $relPathAndFile
                        );
                        print "<div class='cleanItem'>";
                        print $relPathAndFile;
                        print "</div>";
                        $found = true;
                    }
                    asort($files);
                    if (!$found) {
                        print "<div class='cleanNotFound'>";
                        print "No files found<br>";
                        print "</div>";
                    }
                    print "</div>";

                    // Cleanup Thumbnail cache
                    print "<div class='cleanWrapper'>";
                    print "<div class='cleanTitle'>";
                    print "<strong>Cleanup cached thumbnails</strong>";
                    print "</div>";
                    $found = false;
                    if (($dirs = scandir(__DIR__ . '/cache/')) !== false) {
                        foreach ($dirs as $dirIndex => $dirItem) {
                            if ($dirItem === '.') continue;
                            if ($dirItem === '..') continue;
                            if (pathinfo($dirItem, PATHINFO_EXTENSION) !== 'thumb') continue;
                            if (!is_file(__DIR__ . '/cache/' . $dirItem)) continue;
                            $dirItemHash = substr($dirItem, 0, strpos($dirItem, '.'));  // Filename: ...hash....thumb
                            if (!array_key_exists($dirItemHash, $files)) {
                                print "<div class='cleanItem'>";
                                print "Remove ophaned thumbnail: " . $dirItem;
                                print "</div>";
                                unlink(__DIR__ . '/cache/' . $dirItem);
                                $found = true;
                            }
                        }
                    }
                    if (!$found) {
                        print "<div class='cleanNotFound'>";
                        print "No ophaned thumbnails found<br>";
                        print "</div>";
                    }
                    print "</div>";

                    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
                        if (!is_dir(__DIR__ . '/plugins/' . basename($pluginDir))) continue;
                        print "<div class='cleanWrapper'>";
                        print "<div class='cleanTitle'>";
                        print "<strong>Cleanup plugin: " . basename($pluginDir) . "</strong>";
                        print "</div>";
                        if (file_exists(__DIR__ . '/plugins/' . basename($pluginDir) . '/cleanup.php')) {
                            try {
                                include(__DIR__ . '/plugins/' . basename($pluginDir) . '/cleanup.php');
                            } catch (\Throwable $e) {
                                print 'Exception: ' . $e->getMessage();
                            }
                        } else {
                            print "<div class='cleanNotFound'>";
                            print "No cleanup required<br>";
                            print "</div>";
                        }
                        print "</div>";
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
