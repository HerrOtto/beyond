<?php

header('Content-type: text/html; Charset=UTF-8');
require_once __DIR__ . '/inc/init.php';
if (!$tools->checkRole('admin,view')) {
    header('Location: ' . $config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

require_once __DIR__ . '/api/classes/apis.php';
$apis = new apis($config, $variable, $db, $prefix, $languages, $tools);

?>
<html>
<head>
    <title>API</title>
    <?php require_once __DIR__ . '/inc/head.php'; ?>

    <!-- ace editor -->
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/ace.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/theme-chrome.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-javascript.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-php.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-css.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-perl.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-python.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-html.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-ruby.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-sql.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-sh.js"
            type="text/javascript" charset="utf-8"></script>
    <script src="<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/assets/ace-1.4.12/build/src/mode-xml.js"
            type="text/javascript" charset="utf-8"></script>

    <style>

        .apiItem {
            display: table;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 4px;
            cursor: pointer;
        }

        .apiItem:hover {
            border: 1px solid #c0c0c0;
            background-color: #f0f0f0;
        }

        .apiItemIcon {
            display: table-cell;
            padding-left: 5px;
            padding-right: 5px;
            width: .5%;
        }

        .apiItemName {
            display: table-cell;
            width: 99%;
        }

        .apiItemAction {
            display: table-cell;
            width: .5%;
            padding-left: 5px;
            padding-right: 5px;
        }

    </style>

    <script>

        function apiCreate(apiName) {
            <?php print $prefix; ?>api.apis.apiCreate({
                'apiName': apiName
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.apiCreate === true) {
                        location.href = '<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/apis.php?edit=' + encodeURIComponent(apiName) + '&dir=<?php print urlencode($dir['relPath']) . '&nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';
                    } else {
                        message('API file [' + apiName + '] creation failed');
                    }
                }
            });
        }

        function apiDelete(apiNameBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogApiDelete .modal-body').html('Delete API file: <b>' + atob(apiNameBase64) + '</b>');
                $('#dialogApiDelete').data('apiName', atob(apiNameBase64)).modal('show');
                return false;
            }
            <?php print $prefix; ?>api.apis.apiDelete({
                'apiName': atob(apiNameBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.apiDelete === true) {
                        location.href = '<?php print $config->get('base', 'server.baseUrl'); ?>/beyond/apis.php<?php print urlencode($dir) . '?nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10))); ?>';
                    } else {
                        message('API file [' + atob(apiNameBase64) + '] deletion failed');
                    }
                }
            });
        }

        var editor = false;
        var editorApiName = '';

        function apiEdit(apiNameBase64, kind) {
            <?php print $prefix; ?>api.apis.apiLoad({
                'apiName': atob(apiNameBase64),
                'kind': kind
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.apiLoad === true) {
                        $('#list').hide();
                        $('#editor').show();

                        if (kind == 'plugin') {
                            $('#saveButton').hide();
                        } else if (kind === 'internal') {
                            $('#saveButton').hide();
                        } else {
                            $('#saveButton').show();
                        }

                        editorApiName = atob(apiNameBase64);

                        editor = editor = ace.edit("aceEditor");
                        editor.setTheme("ace/theme/chrome");
                        editor.setOptions({
                            autoScrollEditorIntoView: true,
                            //copyWithEmptySelection: true,
                            mergeUndoDeltas: "always"
                        });

                        var phpMode = ace.require("ace/mode/php").Mode;
                        editor.session.setMode(new phpMode());

                        editor.setValue(data.apiContent);
                        editor.gotoLine(0);
                        editor.session.setTabSize(4);
                        editor.session.setUseSoftTabs(true);
                        editor.setHighlightActiveLine(true);
                        document.getElementById('aceEditor').style.fontSize = '12pt';

                        editorResize();
                    } else {
                        message('API file [' + atob(apiNameBase64) + '] loading failed');
                    }
                }
            });
        }

        function apiSave() {
            if (editor === false) {
                message('Editor not initialized');
                return false;
            }
            <?php print $prefix; ?>api.apis.apiSave({
                'apiName': editorApiName,
                'content': editor.getValue()
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.apiSave === true) {

                        $('#saveButton').html('API saved :-)');
                        setTimeout(function () {
                            $('#saveButton').html('Save API (Ctrl+S)');
                        }, 2000);

                    } else {
                        message('API file [' + editorApiName + '] save failed');
                    }
                }
            });
        }

        function apiClose() {
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
            $('#editor').css('height', (window.innerHeight - 180).toString() + 'px');
            editor.resize();
        }

        $(function () {

            $(window).on('resize', function () {
                if (editor !== false) {
                    editorResize();
                }
            });

            // Modal: New api (On show)
            $('#dialogApiAdd').on('shown.bs.modal', function (e) {
                $('#apiName').focus();
            });

            // Ctrl+S -> Save file
            $(window).bind('keydown', function (event) {
                if (event.ctrlKey || event.metaKey) {
                    switch (String.fromCharCode(event.which).toLowerCase()) {
                        case 's':
                            event.preventDefault();
                            if (editor !== false) {
                                apiSave();
                            }
                            break;
                    }
                }
            });

            // Open editor on file creation
            <?php
            $editApiName = $variable->get('edit', '');
            print 'var editApiName = ' . json_encode($editApiName) . ';' . PHP_EOL;
            ?>
            if (editApiName != '') {
                apiEdit(btoa(editApiName), 'site');
            }

        });

    </script>
</head>
<body class="sb-nav-fixed">
<?php require_once __DIR__ . '/inc/begin.php'; ?>
<?php require_once __DIR__ . '/inc/menuTop.php'; ?>
<div id="layoutSidenav">
    <?php require_once __DIR__ . '/inc/menuSide.php'; ?>

    <!-- Create api -->
    <div class="modal fade" id="dialogApiAdd" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form onsubmit="return false;">
                        <div class="form-group">
                            <label class="small mb-1" for="apiName">API class name</label>
                            <input class="form-control py-4" id="apiName" type="text"
                                   placeholder="Enter new file name here"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button" onclick="apiCreate($('#apiName').val());">
                        Add API
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete api -->
    <div class="modal fade" id="dialogApiDelete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Delete file: ...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="apiDelete(btoa($('#dialogApiDelete').data('apiName')), true);">
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
                <?php require_once __DIR__ . '/inc/beginSite.php'; ?>

                <ol class="breadcrumb mb-4 mt-4">
                    <li class="breadcrumb-item active">API</li>
                </ol>

                <?php

                // Initialize listing
                $outputSite = '';

                // List classes files
                $dirs = scandir($config->get('base', 'server.absPath', '') . '/beyond/config/siteClasses', SCANDIR_SORT_ASCENDING);
                foreach ($dirs as $dirIndex => $dirItem) {
                    if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('php'))) {
                        $icon = "fa-file-code";
                    } else {
                        continue;
                    }

                    $outputSite .= '<div class="apiItem">';
                    $outputSite .= '<span class="apiItemIcon" onclick="apiEdit(\'' . base64_encode(basename($dirItem, '.php')) . '\', \'site\');">';
                    $outputSite .= '<i class="fas ' . $icon . '"></i>';
                    $outputSite .= '</span>';
                    $outputSite .= '<span class="apiItemName" onclick="apiEdit(\'' . base64_encode(basename($dirItem, '.php')) . '\', \'site\');">';
                    $outputSite .= basename($dirItem, '.php');
                    $outputSite .= '</span>';
                    $outputSite .= '<span class="apiItemAction" onclick="apiDelete(\'' . base64_encode(basename($dirItem, '.php')) . '\');">';
                    $outputSite .= '<i class="fas fa-trash"></i>';
                    $outputSite .= '</span>';
                    $outputSite .= '</div>' . PHP_EOL;
                    $outputSite .= '<div style="clear: both;"></div>' . PHP_EOL;
                }

                // Initialize internal classes listing
                $output = '';

                // List classes files
                $dirs = scandir($config->get('base', 'server.absPath', '') . '/beyond/api/classes', SCANDIR_SORT_ASCENDING);
                foreach ($dirs as $dirIndex => $dirItem) {
                    if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('php'))) {
                        $icon = "fa-file-code";
                    } else {
                        continue;
                    }

                    $output .= '<div class="apiItem">';
                    $output .= '<span class="apiItemIcon" onclick="apiEdit(\'' . base64_encode(basename($dirItem, '.php')) . '\', \'internal\');">';
                    $output .= '<i class="fas ' . $icon . '"></i>';
                    $output .= '</span>';
                    $output .= '<span class="apiItemName" onclick="apiEdit(\'' . base64_encode(basename($dirItem, '.php')) . '\', \'internal\');">';
                    $output .= basename($dirItem, '.php');
                    $output .= '</span>';
                    $output .= '<span class="apiItemAction">';
                    $output .= '</span>';
                    $output .= '</div>' . PHP_EOL;
                    $output .= '<div style="clear: both;"></div>' . PHP_EOL;

                }

                // Initialize plugin listing
                $outputPlugins = '';

                // List plugin classes files
                foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
                    if (!is_dir($pluginDir)) {
                        continue;
                    }
                    $dirs = scandir($config->get('base', 'server.absPath', '') . '/beyond/plugins/' . basename($pluginDir) . '/apiClasses', SCANDIR_SORT_ASCENDING);
                    foreach ($dirs as $dirIndex => $dirItem) {
                        if (in_array(pathinfo($dirItem, PATHINFO_EXTENSION), array('php'))) {
                            $icon = "fa-file-code";
                        } else {
                            continue;
                        }

                        $outputPlugins .= '<div class="apiItem">';
                        $outputPlugins .= '<span class="apiItemIcon" onclick="apiEdit(\'' . base64_encode(basename($pluginDir) . '_' . basename($dirItem, '.php')) . '\', \'plugin\');">';
                        $outputPlugins .= '<i class="fas ' . $icon . '"></i>';
                        $outputPlugins .= '</span>';
                        $outputPlugins .= '<span class="apiItemName" onclick="apiEdit(\'' . base64_encode(basename($pluginDir) . '_' . basename($dirItem, '.php')) . '\', \'plugin\');">';
                        $outputPlugins .= basename($pluginDir) . '_' . basename($dirItem, '.php');
                        $outputPlugins .= '</span>';
                        $outputPlugins .= '<span class="apiItemAction">';
                        $outputPlugins .= '</span>';
                        $outputPlugins .= '</div>' . PHP_EOL;
                        $outputPlugins .= '<div style="clear: both;"></div>' . PHP_EOL;

                    }
                }

                // Output listing
                print '<div id="list" class="mb-4">' . PHP_EOL;
                print '<div class="text-right mb-4">' . PHP_EOL;
                print '<button class="btn btn-secondary" type="button" data-toggle="modal" data-target="#dialogApiAdd">Add API</button>' . PHP_EOL;
                print '</div>' . PHP_EOL;
                if ($outputSite !== '') {
                    print $outputSite . PHP_EOL;
                }
                if ($output !== '') {
                    print $output . PHP_EOL;
                }
                if ($outputPlugins !== '') {
                    print $outputPlugins . PHP_EOL;
                }
                print '</div>' . PHP_EOL;
                print '</div>' . PHP_EOL;

                // Editor
                print '<div id="editor" class="card mb-4 ml-4 mr-4" style="display:none; border:0px;">' . PHP_EOL;
                print '<div class="card-title text-right">' . PHP_EOL;
                print '<button id="saveButton" class="btn btn-success" type="button" onclick="apiSave();">Save API (Ctrl+S)</button>' . PHP_EOL;
                print '<button class="btn btn-danger" type="button" onclick="apiClose();">Close API</button>' . PHP_EOL;
                print '</div>' . PHP_EOL;
                print '<div class="card-body p-0" style="border:1px solid silver;" id="aceEditor">' . PHP_EOL;
                print '</div>' . PHP_EOL;
                ?>
                <?php require_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
