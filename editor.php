<?php

header('Content-type: text/html; Charset=UTF-8');
require_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    // Is not admin
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

// Check current working directory from browser
$dir = $beyond->tools->checkDirectory($beyond->variable->get('dir', ''));

// Check current file
$editFile = $beyond->variable->get('file', '');

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
    <title>Edit file</title>
    <?php require_once __DIR__ . '/inc/head.php'; ?>

    <!-- ace editor -->
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/ace.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/theme-chrome.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-javascript.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-php.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-css.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-html.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-sql.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-sh.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-xml.js"
            type="text/javascript" charset="utf-8"></script>

    <style>
        .pluginItem {
            border: 0px;
            background-color: #e9ecef;
            padding: 4px;
            margin-bottom: 10px;
        }

        .pluginItemContent {
            margin-bottom: 10px;
        }
    </style>

    <style>
        /* TODO - Splitter

          .splitter {
              width: 100%;
              height: 100px;
              display: flex;
          }

          #separator {
              cursor: col-resize;
              background-color: #aaa;
              background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='30'><path d='M2 0 v30 M5 0 v30 M8 0 v30' fill='none' stroke='black'/></svg>");
              background-repeat: no-repeat;
              background-position: center;
              width: 10px;
              height: 100%;
              -moz-user-select: none;
              -ms-user-select: none;
              user-select: none;
          }

          #first {
              background-color: #dde;
              width: 20%;
              height: 100%;
              min-width: 10px;
          }

          #second {
              background-color: #eee;
              width: 80%;
              height: 100%;
              min-width: 100px;
          }
         */
    </style>

    <script>

        // -------------------------------------------------------------------------------------------------------------

        var pluginSaveHandler = new Array();

        var editor = false;
        var editorFileName = '';

        function fileEdit(fileBase64, fileExtensionBase64) {

            <?php print $beyond->prefix; ?>api.beyondFiles.fileLoad({
                'file': base64decode(fileBase64),
                'currentPath': <?php print json_encode($dir['relPath']); ?>
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.fileLoad === true) {
                        $('#list').hide();
                        $('#editor').show();

                        editorFileName = base64decode(fileBase64);

                        editor = editor = ace.edit("aceEditor");
                        editor.setTheme("ace/theme/chrome");
                        editor.setOptions({
                            autoScrollEditorIntoView: true,
                            //copyWithEmptySelection: true,
                            mergeUndoDeltas: "always"
                        });

                        if (base64decode(fileExtensionBase64) === 'js') {
                            var javascriptMode = ace.require("ace/mode/javascript").Mode;
                            editor.session.setMode(new javascriptMode());
                        } else if (base64decode(fileExtensionBase64) === 'php') {
                            var phpMode = ace.require("ace/mode/php").Mode;
                            editor.session.setMode(new phpMode());
                        } else if (base64decode(fileExtensionBase64) === 'css') {
                            var cssMode = ace.require("ace/mode/css").Mode;
                            editor.session.setMode(new cssMode());
                        } else if (base64decode(fileExtensionBase64) === 'html') {
                            var htmlMode = ace.require("ace/mode/html").Mode;
                            editor.session.setMode(new htmlMode());
                        } else if (base64decode(fileExtensionBase64) === 'htm') {
                            var htmlMode = ace.require("ace/mode/html").Mode;
                            editor.session.setMode(new htmlMode());
                        } else if (base64decode(fileExtensionBase64) === 'sql') {
                            var sqlMode = ace.require("ace/mode/sql").Mode;
                            editor.session.setMode(new sqlMode());
                        } else if (base64decode(fileExtensionBase64) === 'sh') {
                            var shMode = ace.require("ace/mode/sh").Mode;
                            editor.session.setMode(new shMode());
                        } else if (base64decode(fileExtensionBase64) == 'xml') {
                            var xmlMode = ace.require("ace/mode/xml").Mode;
                            editor.session.setMode(new xmlMode());
                        }

                        editor.setValue(data.fileContent);
                        editor.gotoLine(0);
                        editor.session.setTabSize(4);
                        editor.session.setUseSoftTabs(true);
                        editor.setHighlightActiveLine(true);
                        document.getElementById('aceEditor').style.fontSize = '12pt';

                        editorResize();
                    } else {
                        message('Loading file [' + base64decode(fileBase64) + '] failed');
                    }
                }
            });
        }

        function fileSave() {
            if (editor === false) {
                message('Editor not initialized');
                return false;
            }
            <?php print $beyond->prefix; ?>api.beyondFiles.fileSave({
                'file': editorFileName,
                'content': editor.getValue(),
                'currentPath': <?php print json_encode($dir['relPath']); ?>
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.fileSave === true) {

                        // Call plugin save handler
                        for (handler in pluginSaveHandler) {
                            pluginSaveHandler[handler](editorFileName, <?php print json_encode($dir['relPath']); ?>);
                        }

                        // Change buttons
                        $('#saveButton').html('File saved :-)');
                        setTimeout(function () {
                            $('#saveButton').html('Save file (Ctrl+S)');
                        }, 2000);

                    } else {
                        message('File [' + editorFileName + '] save failed');
                    }
                }
            });
        }

        function fileClose() {
            $('#editor').hide();
            $('#list').show();

            if (editor !== false) {
                editor.destroy();
                editor = false;
                editorFileName = '';
                $('#aceEditor').empty();
            }
        }

        function editorResize() {
            var marginTop = 240;

            if ($('#plugins').length > 0) {
                // Plugins installed
                if ($('#plugins').offset().top != $('#aceEditor').offset().top) {
                    // Low-res plugins above editor
                    $('#plugins').css('height', 'auto');
                    $('#pluginsContent').css('height', 'auto');
                    $('#pluginsContent').css('overflow-x', 'initial');
                    $('#pluginsContent').css('overflow-y', 'initial');
                    $('#pluginsContent').css('margin-right', '0px');
                    $('#pluginsContent').css('margin-bottom', '40px');
                    $('#pluginsContent').css('padding-right', '0px');
                    $('#aceEditor').css('height', (window.innerHeight - marginTop).toString() + 'px');
                } else {
                    // High res plugins left to editor
                    $('#plugins').css('height', (window.innerHeight - marginTop).toString() + 'px');
                    $('#pluginsContent').css('padding-right', '10px');
                    $('#pluginsContent').css('margin-right', '10px');
                    $('#pluginsContent').css('margin-bottom', '0px');
                    $('#pluginsContent').css('height', (window.innerHeight - marginTop).toString() + 'px');
                    $('#pluginsContent').css('overflow-x', 'hidden');
                    $('#pluginsContent').css('overflow-y', 'scroll');
                    $('#aceEditor').css('height', (window.innerHeight - marginTop).toString() + 'px');
                }
            } else {
                // No plugins installed
                $('#aceEditor').css('height', (window.innerHeight - marginTop).toString() + 'px');
            }

            editor.resize();
        }

        // -------------------------------------------------------------------------------------------------------------

        /* TODO - Splitter
        function dragElement(element, direction) {
            var md; // remember mouse down info
            const first = document.getElementById("first");
            const second = document.getElementById("second");

            element.onmousedown = onMouseDown;

            function onMouseDown(e) {
                //console.log("mouse down: " + e.clientX);
                md = {
                    e,
                    offsetLeft: element.offsetLeft,
                    offsetTop: element.offsetTop,
                    firstWidth: first.offsetWidth,
                    secondWidth: second.offsetWidth
                };

                document.onmousemove = onMouseMove;
                document.onmouseup = () => {
                    //console.log("mouse up");
                    document.onmousemove = document.onmouseup = null;
                }
            }

            function onMouseMove(e) {
                //console.log("mouse move: " + e.clientX);
                var delta = {
                    x: e.clientX - md.e.clientX,
                    y: e.clientY - md.e.clientY
                };

                if (direction === "H") // Horizontal
                {
                    // Prevent negative-sized elements
                    delta.x = Math.min(Math.max(delta.x, -md.firstWidth),
                        md.secondWidth);

                    element.style.left = md.offsetLeft + delta.x + "px";
                    first.style.width = (md.firstWidth + delta.x) + "px";
                    second.style.width = (md.secondWidth - delta.x) + "px";
                }
            }
        }
         */

        // -------------------------------------------------------------------------------------------------------------

        $(function () {

            // Resize editor on browser resize
            $(window).on('resize', function () {
                if (editor !== false) {
                    editorResize();
                }
            });

            // Ctrl+S -> Save file
            $(window).bind('keydown', function (event) {
                if (event.ctrlKey || event.metaKey) {
                    switch (String.fromCharCode(event.which).toLowerCase()) {
                        case 's':
                            event.preventDefault();
                            if (editor !== false) {
                                fileSave();
                            }
                            break;
                    }
                }
            });

            <?php
            // Open editor on file creation
            print 'var editFile = ' . json_encode($editFile) . ';' . PHP_EOL;
            print 'var editExtension = ' . json_encode(strtolower(pathinfo($editFile, PATHINFO_EXTENSION))) . ';' . PHP_EOL;
            ?>
            if (editFile != '') {
                fileEdit(base64encode(editFile), base64encode(editExtension));
            }

            // TODO - Splitter: dragElement(document.getElementById("separator"), "H");

        });
    </script>

    <?php
    // Check for editor plugins
    $editorPluginsInstalled = false;
    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
        if (!is_dir($pluginDir)) {
            continue;
        }
        if (file_exists($pluginDir . '/editorHead.php')) {
            try {
                if (ob_start()) {
                    include $pluginDir . '/editorHead.php';
                    $res = ob_get_contents();
                    ob_end_clean();
                    print $res;
                }
                $editorPluginsInstalled = true;
            } catch (Exception $e) {
                ob_end_clean();
                print "<!-- " . $pluginDir . '/editorHead.php - Exception: ' . $e->getMessage() . ' -->' . PHP_EOL;
            }
        }
    }
    unset($pluginDir);
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

                <ol class="breadcrumb mb-4 mt-4"><?php
                    if ($dir['isValid'] !== true) {
                        print '<li class="breadcrumb-item">Edit</li>'; // Directory is not valid
                    } else if ($dir['relPath'] === '') {
                        print '<li class="breadcrumb-item">Edit</li>'; // Base directory
                        print '<li class="breadcrumb-item active">' . $editFile . '</li>';
                    } else {
                        print '<li class="breadcrumb-item"><a href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php?dir=">Edit</a></li>';

                        $dirParts = explode('/', $dir['relPath']);
                        $dirCurrent = '';
                        foreach ($dirParts as $dirPartIndex => $dirPartItem) {
                            $dirCurrent .= '/' . $dirPartItem;
                            print '<li class="breadcrumb-item"><a href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php?dir=' . urlencode($dirCurrent) . '">' . $dirPartItem . '</a></li>';
                        }
                        unset($dirParts);
                        unset($dirCurrent);
                        unset($dirPartIndex);
                        unset($dirPartItem);

                        print '<li class="breadcrumb-item active">' . $editFile . '</li>';
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

                // Editor
                $backUrl = $beyond->config->get('base', 'server.baseUrl') .
                    '/beyond/files.php' .
                    '?dir=' . urlencode($beyond->variable->get('dir', '')) .
                    '&nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10)));

                print '<div id="editor" class="card mb-3 ml-3 mr-3" style="display:none; border:0px;">' . PHP_EOL; # card
                print '  <div class="card-body p-0">' . PHP_EOL;
                print '    <div class="row">';
                print '    <div class="col-12 text-right mt-0 ml-0 mr-0 mb-4 p-0">';
                print '    <button id="saveButton" class="btn btn-success" type="button" onclick="fileSave();">Save file (Ctrl+S)</button>' . PHP_EOL;
                print '    <button id="saveButton" class="btn btn-secondary" type="button" onclick="window.open(\'' . $beyond->config->get('base', 'server.baseUrl') . '/' . $dir['relPath'] . '/' . $editFile . '\',\'_blank\');">Open in new tab</button>' . PHP_EOL;
                print '    <button class="btn btn-danger" type="button" onclick="location.href = \'' . $backUrl . '\';">Close file</button>' . PHP_EOL;
                print '    </div>' . PHP_EOL;
                print '    </div>' . PHP_EOL;
                print '    <div class="row">' . PHP_EOL;

                /* TODO-Splitter
                if ($editorPluginsInstalled) {
                    print '<div class="splitter">' . PHP_EOL;
                    print '  <div id="first">' . PHP_EOL;
                    print '    <div id="pluginsContent">' . PHP_EOL;
                    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
                        if (!is_dir($pluginDir)) {
                            continue;
                        }
                        if (file_exists($pluginDir . '/editor.php')) {
                            try {
                                print '<div class="pluginItem">Plugin: ' . basename($pluginDir) . '</div>' . PHP_EOL;
                                require_once $pluginDir . '/editor.php';
                            } catch (Exception $e) {
                                $beyond->exceptionHandler->add($e);
                            }
                        }
                    }
                    print '    </div>' . PHP_EOL;
                    print '  </div>' . PHP_EOL;
                    print '  <div id="separator" ></div>' . PHP_EOL;
                    print '  <div id="second" >' . PHP_EOL;
                    print '    <div class="col-12 m-0 p-0" id="aceEditor" style="border:1px solid silver;">' . PHP_EOL;
                    print '    </div>' . PHP_EOL; # /col right
                    print '  </div>' . PHP_EOL;
                    print '</div>' . PHP_EOL;
                } else {
                    print '<div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 m-0 p-0" id="aceEditor" style="border:1px solid silver;">' . PHP_EOL;
                    print '</div>' . PHP_EOL; # /col full width (no plugins)
                }
                */

                if ($editorPluginsInstalled) {
                    print '      <div id="plugins" class="col-xl-4 col-lg-12 col-md-12 col-sm-12 m-0 p-0">' . PHP_EOL;
                    print '        <div id="pluginsContent">' . PHP_EOL;
                    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
                        if (!is_dir($pluginDir)) {
                            continue;
                        }
                        if (file_exists($pluginDir . '/editor.php')) {
                            try {
                                $pluginError = false;
                                $plugin = '';
                                $plugin .= '<div class="pluginItem">Plugin: ' . basename($pluginDir) . '</div>' . PHP_EOL;
                                $plugin .= '<div class="pluginItemContent">' . PHP_EOL;
                                ob_start();
                                try {
                                    include $pluginDir . '/editor.php';
                                    $plugin .= ob_get_contents();
                                } catch (Exception $e) {
                                    $pluginError = true;
                                }
                                ob_end_clean();
                                $plugin .= '</div>' . PHP_EOL;
                                if (!$pluginError) {
                                    print $plugin;
                                }
                            } catch (Exception $e) {
                                $beyond->exceptionHandler->add($e);
                            }
                        }
                    }
                    print '        </div>' . PHP_EOL;
                    print '      </div>' . PHP_EOL; # /col left
                }

                if ($editorPluginsInstalled) {
                    print '<div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 m-0 p-0" id="aceEditor" style="border:1px solid silver;">' . PHP_EOL;
                    print '</div>' . PHP_EOL; # /col full width (no plugins)
                } else {
                    print '<div class="col-12 m-0 p-0" id="aceEditor" style="border:1px solid silver;">' . PHP_EOL;
                    print '      </div>' . PHP_EOL; # /col right
                }

                print '    </div>' . PHP_EOL; # /row
                print '  </div>' . PHP_EOL; # /card-body
                print '</div>' . PHP_EOL; # /card

                ?>
                <?php require_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
