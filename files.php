<?php

header('Content-type: text/html; Charset=UTF-8');
include_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    // Is not admin or viewer
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

// Check current working directory from browser
$dir = $beyond->tools->checkDirectory($beyond->variable->get('dir', ''));

// Code/Text - Edit in editor
$extCode = array(
    // Code
    'js', 'php', 'css', 'pl', 'py', 'html', 'htm', 'rb', 'sql', 'sh', 'xml',
    // Text
    '.txt', '.ini'
    // .htaccess
);

// Images
$extImages = array('jpg', 'jpeg', 'png', 'gif');

// Thumbnail size
$width = 300;
$height = 225;

?>
<html>
<head>
    <title>Files</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>

    <style>

        .fileItem {
            display: table;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 4px;
            cursor: pointer;
        }

        .fileItem:hover {
            border: 1px solid #c0c0c0;
            background-color: #f0f0f0;
        }

        .fileItemIcon {
            display: table-cell;
            padding-left: 5px;
            padding-right: 5px;
            width: .5%;
        }

        .fileItemName {
            display: table-cell;
            width: 99%;
        }

        .fileItemAction {
            display: table-cell;
            width: .5%;
            padding-left: 5px;
            padding-right: 5px;
        }

        .imageItemOuter {
            float: left;
            position: relative;
            overflow: hidden;
            padding: 10px;
        }

        .imageItemImage > img {
            width: 100%;
            border-radius: 4px;
        }

        .imageItemFile {
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            padding: 4px;
            width: 100%;
        }

        .imageItemDelete {
            position: absolute;
            top: 25px;
            right: 30px;
            text-shadow: -1px -1px 0 #ffffff, 1px -1px 0 #ffffff, -1px 1px 0 #ffffff, 1px 1px 0 #ffffff;
            color: red;
            cursor: pointer;
        }

        .imageItemDownload {
            position: absolute;
            top: 25px;
            left: 30px;
            text-shadow: -1px -1px 0 #ffffff, 1px -1px 0 #ffffff, -1px 1px 0 #ffffff, 1px 1px 0 #ffffff;
            color: lightseagreen;
            cursor: pointer;
        }

        .uploadOverlay {
            z-index: 9998;
            position: fixed;
            top: 0px;
            bottom: 0px;
            left: 0px;
            right: 0px;
            opacity: 0.5;
            background-color: black;
        }

        .uploadStatus {
            z-index: 9999;
            position: fixed;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            font-size: 40pt;
            background-color: black;
            color: white;
            padding: 10px;
        }

    </style>

    <script>

        function directoryCreate(dir) {
            <?php print $beyond->prefix; ?>api.beyondFiles.directoryCreate({
                'directory': dir,
                'currentPath': <?php print json_encode($dir['relPath']); ?>
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.directoryCreate === true) {
                        location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/files.php' +
                            '?dir=<?php print urlencode($dir['relPath']); ?>' +
                            '&nocache=<?php print urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';
                    } else {
                        message('Directory [' + dir + '] creation failed');
                    }
                }
            });
        }

        function directoryDelete(dirBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogDirectoryDelete .modal-body').html('Delete directory: <b>' + base64decode(dirBase64) + '</b>');
                $('#dialogDirectoryDelete').data('directory', base64decode(dirBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.beyondFiles.directoryDelete({
                'directory': base64decode(dirBase64),
                'currentPath': <?php print json_encode($dir['relPath']); ?>
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.directoryDelete === true) {
                        location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/files.php' +
                            '?dir=<?php print urlencode($dir['relPath']); ?>' +
                            '&nocache=<?php print urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';
                    } else {
                        $('#dialogDirectoryDelete').modal('hide');
                        message('Directory [' + base64decode(dirBase64) + '] deletion failed');
                    }
                }
            });
        }

        function fileCreate(fileName) {
            <?php print $beyond->prefix; ?>api.beyondFiles.fileCreate({
                'file': fileName,
                'currentPath': <?php print json_encode($dir['relPath']); ?>
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.fileCreate === true) {
                        location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/editor.php?file=' + encodeURIComponent(fileName) + '&dir=<?php print urlencode($dir['relPath']) . '&nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';
                    } else {
                        message('File [' + fileName + '] creation in directory [<?php print json_encode($dir['relPath']); ?>] failed');
                    }
                }
            });
        }

        function fileDelete(fileBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogFileDelete .modal-body').html('Delete file: <b>' + base64decode(fileBase64) + '</b>');
                $('#dialogFileDelete').data('fileName', base64decode(fileBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.beyondFiles.fileDelete({
                'file': base64decode(fileBase64),
                'currentPath': <?php print json_encode($dir['relPath']); ?>
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.fileDelete === true) {
                        location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/files.php?dir=<?php print urlencode($dir['relPath']) . '&nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';
                    } else {
                        message('File [' + base64decode(fileBase64) + '] deletion failed');
                    }
                }
            });
        }

        function resizeImages() {
            var width = $('#layoutSidenav_content .container-fluid').width();
            var images = Math.ceil(width / (<?php print $width; ?>-20));
            $('.imageItemOuter').css('width', (100 / images) + '%');
        }

        $(function () {

            // Resize images on browser resize
            $(window).on('resize', function () {
                resizeImages();
            });
            resizeImages();

            // Modal: New file (On show)
            $('#dialogFileAdd').on('shown.bs.modal', function (e) {
                $('#fileName').focus();
            });

            // Modal: New directory (On show)
            $('#dialogDirectoryAdd').on('shown.bs.modal', function (e) {
                $('#directoryName').focus();
            });

        });

        // -------------------------------------------------------------------------------------------------------------

        var uploading = false;

        $(function () {

            $('body').on(
                'dragover',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (uploading !== false) {
                        return;
                    }
                    $('body').scrollTop();
                }
            );

            $('body').on(
                'dragenter',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (uploading !== false) {
                        return;
                    }
                    $('#uploadOverlay').show();
                    $('#uploadStatus').html('Drop file to upload');
                    $('#uploadStatus').show();

                }
            );

            $('body').on(
                'dragleave',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (uploading !== false) {
                        return;
                    }

                    if (window.event.pageX == 0 || window.event.pageY == 0) {
                        $('#uploadOverlay').hide();
                        $('#uploadStatus').html('');
                        $('#uploadStatus').hide();
                        return false;
                    }

                }
            );

            $("html").on(
                'drop',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (uploading !== false) {
                        return;
                    }

                    var file = e.originalEvent.dataTransfer.files;
                    var fd = new FormData();
                    fd.append('file', file[0]);
                    fd.append('fileName', file[0].name);
                    fd.append('dir', <?php print json_encode($dir['relPath'] . '/' . $dirItem); ?>);
                    uploadData(fd);
                }
            );

            function uploadData(formdata) {
                uploading = true;
                $('#uploadStatus').html('Uploading file...');

                $.ajax({
                    url: 'upload.php',
                    type: 'post',
                    data: formdata,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.error === false) {
                            $('#uploadStatus').html('Done');
                            location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>' +
                                '/beyond/files.php' +
                                '?dir=<?php print urlencode($dir['relPath'] . '/' . $dirItem); ?>' +
                                '&nocache=<?php print urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';

                        } else {
                            $('#uploadOverlay').hide();
                            $('#uploadStatus').html('');
                            $('#uploadStatus').hide();
                            uploading = false;
                            message(response.error);
                        }
                    }
                });
            }

        });

    </script>
</head>
<body class="sb-nav-fixed">

<div id="uploadOverlay" class="uploadOverlay" style="display:none;"></div>
<div id="uploadStatus" class="uploadStatus" style="display:none;">...</div>

<?php include_once __DIR__ . '/inc/begin.php'; ?>
<?php include_once __DIR__ . '/inc/menuTop.php'; ?>
<div id="layoutSidenav">
    <?php include_once __DIR__ . '/inc/menuSide.php'; ?>

    <!-- Create directory -->
    <div class="modal fade" id="dialogDirectoryAdd" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form onsubmit="return false;">
                        <div class="form-group">
                            <label class="small mb-1" for="directoryName">Directory name</label>
                            <input class="form-control py-4" id="directoryName" type="text"
                                   placeholder="Enter new directory name here"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button" onclick="directoryCreate($('#directoryName').val());">
                        Add directory
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete directory -->
    <div class="modal fade" id="dialogDirectoryDelete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Delete directory: ...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="directoryDelete(base64encode($('#dialogDirectoryDelete').data('directory')), true);">
                        Delete directory
                    </button>
                    <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create file -->
    <div class="modal fade" id="dialogFileAdd" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form onsubmit="return false;">
                        <div class="form-group">
                            <label class="small mb-1" for="fileName">File name</label>
                            <input class="form-control py-4" id="fileName" type="text"
                                   placeholder="Enter new file name here"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button" onclick="fileCreate($('#fileName').val());">
                        Add file
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete file -->
    <div class="modal fade" id="dialogFileDelete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Delete file: ...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="fileDelete(base64encode($('#dialogFileDelete').data('fileName')), true);">
                        Delete file
                    </button>
                    <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <?php include_once __DIR__ . '/inc/beginSite.php'; ?>

                <ol class="breadcrumb mb-4 mt-4"><?php
                    if ($dir['isValid'] !== true) {
                        print '<li class="breadcrumb-item">Files</li>'; // Directory is not valid
                    } else if ($dir['relPath'] === '') {
                        print '<li class="breadcrumb-item">Files</li>'; // Base directory
                    } else {
                        print '<li class="breadcrumb-item"><a href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php?dir=">Files</a></li>';

                        $dirParts = explode('/', $dir['relPath']);
                        $dirCurrent = '';
                        foreach ($dirParts as $dirPartIndex => $dirPartItem) {
                            $dirCurrent .= '/' . $dirPartItem;
                            if (count($dirParts) - 1 == $dirPartIndex) {
                                print '<li class="breadcrumb-item active">' . $dirPartItem . '</li>';
                            } else {
                                print '<li class="breadcrumb-item"><a href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php?dir=' . urlencode($dirCurrent) . '">' . $dirPartItem . '</a></li>';
                            }
                        }
                        unset($dirParts);
                        unset($dirCurrent);
                        unset($dirPartIndex);
                        unset($dirPartItem);
                    }
                    ?>
                </ol>

                <?php

                // Output directory error
                if ($dir['isValid'] !== true) {
                    print '<div class="card mb-4">' . PHP_EOL;
                    print '<div class="card-body">' . PHP_EOL;
                    print $dir['isValid'] . PHP_EOL;
                    print '</div>' . PHP_EOL;
                    print '</div>' . PHP_EOL;
                }

                // Initialize listing
                $output = '';

                // List directorys
                if ($dir['isValid'] === true) {
                    if (($dirs = scandir($dir['absPath'], SCANDIR_SORT_ASCENDING)) !== false) {
                        foreach ($dirs as $dirIndex => $dirItem) {
                            if ($dirItem === '.') continue;
                            if ($dirItem === '..') continue;
                            if ($dirItem === 'beyond') continue;
                            if (!is_dir($dir['absPath'] . '/' . $dirItem)) {
                                continue;
                            }

                            $location =
                                $beyond->config->get('base', 'server.baseUrl') .
                                '/beyond/files.php' .
                                '?dir=' . urlencode($dir['relPath'] . '/' . $dirItem) .
                                '&nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10)));

                            $output .= '<div class="fileItem">';
                            $output .= '<span class="fileItemIcon" onclick="location.href=\'' . $location . '\';">';
                            $output .= '<i class="fas fa-folder"></i>';
                            $output .= '</span>';
                            $output .= '<span class="fileItemName" onclick="location.href=\'' . $location . '\';">';
                            $output .= $dirItem;
                            $output .= '</span>';
                            $output .= '<span class="fileItemAction" onclick="directoryDelete(\'' . base64_encode($dirItem) . '\');">';
                            $output .= '<i class="fas fa-trash"></i>';
                            $output .= '</span>';
                            $output .= '</div>' . PHP_EOL;
                            $output .= '<div style="clear: both;"></div>' . PHP_EOL;
                        }
                    }
                }

                // List files
                if ($dir['isValid'] === true) {
                    $itemNo = 0;
                    if (($dirs = scandir($dir['absPath'], SCANDIR_SORT_ASCENDING)) !== false) {
                        foreach ($dirs as $dirIndex => $dirItem) {
                            if (is_dir($dir['absPath'] . DIRECTORY_SEPARATOR . $dirItem)) continue;
                            if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), $extImages)) continue;

                            if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('php', 'htm', 'html'))) {
                                $icon = "fa-file-code";
                            } else if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('txt', 'htaccess'))) {
                                $icon = "fa-file-alt";
                            } else if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('zip', 'gz', 'tar'))) {
                                $icon = "fa-file-archive";
                            } else if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('pdf'))) {
                                $icon = "fa-file-pdf";
                            } else {
                                $icon = "fa-file";
                            }

                            $itemNo += 1;
                            $output .= '<div class="fileItem">';

                            if (($dirItem == '.htaccess') || (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), $extCode))) {
                                // Edit
                                $editUrl =
                                    $beyond->config->get('base', 'server.baseUrl') .
                                    '/beyond/editor.php' .
                                    '?file=' . urlencode($dirItem) .
                                    '&dir=' . urlencode($dir['relPath']) .
                                    '&nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10)));

                                $output .= '<span class="fileItemIcon" onclick="location.href = \'' . $editUrl . '\';">';
                                $output .= '<i class="fas ' . $icon . '"></i>';
                                $output .= '</span>';
                                $output .= '<span class="fileItemName" onclick="location.href = \'' . $editUrl . '\';">';
                                $output .= $dirItem;
                                $output .= '</span>';

                            } else {
                                // Download
                                $output .= '<span class="fileItemIcon" onclick="$(\'#download-file-' . $itemNo . '\').get(0).click();">';
                                $output .= '<i class="fas ' . $icon . '"></i>';
                                $output .= '</span>';
                                $output .= '<span class="fileItemName" onclick="$(\'#download-file-' . $itemNo . '\').get(0).click();">';
                                $output .= $dirItem;
                                $output .= '<a style="display:none;" id="download-file-' . $itemNo . '" href="' . $beyond->config->get('base', 'server.baseUrl') . '/' . $dir['relPath'] . '/' . $dirItem . '" download="' . $dirItem . '">Download</a>';
                                $output .= '</span>';

                            }

                            $output .= '<span class="fileItemAction" onclick="fileDelete(\'' . base64_encode($dirItem) . '\');">';
                            $output .= '<i class="fas fa-trash"></i>';
                            $output .= '</span>';
                            $output .= '</div>' . PHP_EOL;
                            $output .= '<div style="clear: both;"></div>' . PHP_EOL;

                        }
                    }
                }

                // List images
                $outputImage = '';
                if ($dir['isValid'] === true) {
                    $dirs = scandir($dir['absPath'], SCANDIR_SORT_ASCENDING);
                    foreach ($dirs as $dirIndex => $dirItem) {
                        if (is_dir($dir['absPath'] . DIRECTORY_SEPARATOR . $dirItem)) continue;
                        if (!in_array(pathinfo($dirItem, PATHINFO_EXTENSION), $extImages)) continue;

                        if (pathinfo($dirItem, PATHINFO_EXTENSION) == "gif") {
                            $image = imagecreatefromgif($dir['absPath'] . DIRECTORY_SEPARATOR . $dirItem);
                        } else if (pathinfo($dirItem, PATHINFO_EXTENSION) == "png") {
                            $image = imagecreatefrompng($dir['absPath'] . DIRECTORY_SEPARATOR . $dirItem);
                        } else if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array("jpg", "jpeg"))) {
                            $image = imagecreatefromjpeg($dir['absPath'] . DIRECTORY_SEPARATOR . $dirItem);
                        }

                        $info = getimagesize($dir['absPath'] . DIRECTORY_SEPARATOR . $dirItem);

                        $final_width = 0;
                        $final_height = 0;
                        list($width_old, $height_old) = $info;

                        $factor = min($width / $width_old, $height / $height_old);
                        $final_width = round($width_old * $factor);
                        $final_height = round($height_old * $factor);

                        $image_resized = imagecreatetruecolor($width, $height);
                        $silver = imagecolorallocate($image_resized, 233, 236, 239);
                        imagefill($image_resized, 0, 0, $silver);

                        imagecopyresampled(
                            $image_resized,
                            $image,
                            ($width / 2) - ($final_width / 2),
                            ($height / 2) - ($final_height / 2),
                            0,
                            0,
                            $final_width,
                            $final_height,
                            $width_old,
                            $height_old
                        );

                        // Get image as PNG
                        ob_start();
                        imagepng($image_resized);
                        $pngFile = ob_get_contents();
                        ob_end_clean();

                        imagedestroy($image_resized);
                        imagedestroy($image);

                        $outputImage .= '<div class="imageItemOuter" style="width: ' . $width . ';">';
                        $outputImage .= '    <div class="imageItemImage">';
                        $outputImage .= '      <img src="' . 'data:image/png;base64,' . base64_encode($pngFile) . '" />';
                        $outputImage .= '    </div>';
                        $outputImage .= '    <div class="imageItemFile" title="' . htmlentities($dirItem) . '">';
                        $outputImage .= '      ' . $dirItem;
                        $outputImage .= '    </div>';
                        $outputImage .= '    <div class="imageItemDownload">';
                        $outputImage .= '      <a href="' . $beyond->config->get('base', 'server.baseUrl') . '/' . $dir['relPath'] . '/' . $dirItem . '" download="' . $dirItem . '"><i class="fas fa-download"></i></a>';
                        $outputImage .= '    </div>';
                        $outputImage .= '    <div class="imageItemDelete" onclick="fileDelete(\'' . base64_encode($dirItem) . '\');">';
                        $outputImage .= '      <i class="fas fa-trash"></i>';
                        $outputImage .= '    </div>';
                        $outputImage .= '</div>' . PHP_EOL;
                    }
                }

                // Output listing
                print '<div id="list" class="mb-4">' . PHP_EOL;
                print '<div class="text-right mb-4">' . PHP_EOL;
                print '<button class="btn btn-secondary" type="button" data-toggle="modal" data-target="#dialogFileAdd">Add file</button>' . PHP_EOL;
                print '<button class="btn btn-secondary" type="button" data-toggle="modal" data-target="#dialogDirectoryAdd">Add directory</button>' . PHP_EOL;
                print '</div>' . PHP_EOL;
                if (($output !== '') && ($outputImage !== '')) {
                    print $output . PHP_EOL;
                    print '<br>';
                    print $outputImage . PHP_EOL;
                } else if ($outputImage !== '') {
                    print $outputImage . PHP_EOL;
                } else if ($output !== '') {
                    print $output . PHP_EOL;
                } else {
                    print "Empty directory";
                }
                print '</div>' . PHP_EOL;
                print '</div>' . PHP_EOL;

                unset($output);
                unset($outputImage);

                ?>
                <?php include_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php include_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
