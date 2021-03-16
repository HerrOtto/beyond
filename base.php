<?php

/*
 * This script has to be loaded from all websites that want to use the beyond API:
 *
 * <script type="text/javascript">
 * document.write('<scr'+'ipt src="/beyond/base.php?nocache='+Math.random()+'" type="text/javascript"></scr'+'ipt>');
 * </script>
 *
 */

include_once __DIR__ . '/inc/init.php';

header('Content-type: application/javascript; Charset=UTF-8');
header('Access-Control-Allow-Origin: ' . $beyond->config->get('base', 'api.accessOrigin'));

// -----------------------------------------------------------------------------------------------------------------

print 'var ' . $beyond->prefix . 'languages = ' . json_encode($beyond->languages) . ';' . PHP_EOL;
print 'var ' . $beyond->prefix . 'language = "' . $_SESSION[$beyond->prefix . 'data']['language'] . '";' . PHP_EOL;
print PHP_EOL;

// -----------------------------------------------------------------------------------------------------------------

try {

    print 'function ' . $beyond->prefix . 'apiAjax(request, callBack) {' . PHP_EOL;
    print '    var parameters = \'\';' . PHP_EOL;
    print '    for (key in request.data) {' . PHP_EOL;
    print '        parameters +=' . PHP_EOL;
    print '            (parameters === \'\' ? \'\' : \'&\') +' . PHP_EOL;
    print '            key +' . PHP_EOL;
    print '            \'=\' +' . PHP_EOL;
    print '            encodeURIComponent(request.data[key]);' . PHP_EOL;
    print '    }' . PHP_EOL;
    print '    parameters += \'&nocache=\' + encodeURIComponent(Date.now().toString() + Math.random().toString(20));' . PHP_EOL;
    print '    var xhr = new XMLHttpRequest();' . PHP_EOL;
    print '    xhr.open(' . PHP_EOL;
    print '        \'GET\',' . PHP_EOL;
    print '        request.url + \'?\' + parameters' . PHP_EOL;
    print '    );' . PHP_EOL;
    print '    xhr.onload = function () {' . PHP_EOL;
    print '        if (xhr.status === 200) {' . PHP_EOL;
    print '            var responseTextJson;' . PHP_EOL;
    print '            try {' . PHP_EOL;
    print '              responseTextJson = JSON.parse(xhr.responseText);' . PHP_EOL;
    print '            } catch (e) {' . PHP_EOL;
    print '              callBack(\'JSON parsing failed [\' + e.message + \'] on [\' + xhr.responseText + \']\', {});' . PHP_EOL;
    print '            }' . PHP_EOL;
    print '            try {' . PHP_EOL;
    print '              if (responseTextJson.error !== false) {' . PHP_EOL;
    print '                var error = responseTextJson.error;' . PHP_EOL;
    print '                delete responseTextJson.error;' . PHP_EOL;
    print '                callBack(error, responseTextJson)' . PHP_EOL;
    print '              } else {' . PHP_EOL;
    print '                callBack(false, responseTextJson);' . PHP_EOL;
    print '              }' . PHP_EOL;
    print '            } catch (e) {' . PHP_EOL;
    print '              callBack(\'Exception [\' + e.message + \'] on [callback done]\', {});' . PHP_EOL;
    print '            }' . PHP_EOL;
    print '        } else {' . PHP_EOL;
    print '            callBack(true, xhr.status);' . PHP_EOL;
    print '        }' . PHP_EOL;
    print '    };' . PHP_EOL;
    print '    xhr.send();' . PHP_EOL;
    print '}' . PHP_EOL;
    print  PHP_EOL;

    // -----------------------------------------------------------------------------------------------------------------

    $script = '';

    // API handler
    $script = 'var ' . $beyond->prefix . 'apiAjaxHandlerUrl = \'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/api/apiHandler.php\';' . PHP_EOL . PHP_EOL;

    // API
    $script .= 'var ' . $beyond->prefix . 'api = {' . PHP_EOL . PHP_EOL;

    // Enumerate classes
    foreach (glob(__DIR__ . '/api/classes/*.php') as $classFileName) {
        try {
            $error = true;
            try {
                include_once $classFileName;
                $error = false;
            } catch (\Throwable $e) {
                print '// ' . $e->getMessage() . PHP_EOL;
            }
            if (!$error) {
                // Begin class
                $script .= '  \'' . basename($classFileName, '.php') . '\': {' . PHP_EOL;

                // Functions
                $functions = get_class_methods(basename($classFileName, '.php'));
                foreach ($functions as $functionIndex => $functionItem) {
                    if (preg_match('/^_/', $functionItem)) {
                        continue;
                    }
                    $script .= '    \'' . $functionItem . '\': function(jsonData, ajaxDoneCallBack) { ' . $beyond->prefix . 'apiAjax({\'url\': ' . $beyond->prefix . 'apiAjaxHandlerUrl, \'data\': { \'class\': \'' . basename($classFileName, '.php') . '\', \'call\': \'' . $functionItem . '\', \'data\': JSON.stringify(jsonData) } }, ajaxDoneCallBack); }, ' . PHP_EOL;
                }

                // End class
                $script .= '  },' . PHP_EOL . PHP_EOL;
            }
        } catch (Exception $e) {
            print '// ' . $e->getMessage() . PHP_EOL;
        }
    }

    // Enumerate plugin classes
    foreach (glob(__DIR__ . '/plugins/*') as $pluginDir) {
        if (!is_dir($pluginDir)) {
            continue;
        }
        foreach (glob(__DIR__ . '/plugins/' . basename($pluginDir) . '/apiClasses/*.php') as $classFileName) {
            try {
                $error = true;
                try {
                    include_once __DIR__ . '/plugins/' . basename($pluginDir) . '/apiClasses/' . basename($classFileName);
                    $error = false;
                } catch (\Throwable $e) {
                    print '// ' . $e->getMessage() . PHP_EOL;
                }
                if (!$error) {
                    // Begin class
                    $script .= '  \'' . basename($pluginDir) . '_' . basename($classFileName, '.php') . '\': {' . PHP_EOL;
                    // Functions
                    $functions = get_class_methods(basename($pluginDir) . '_' . basename($classFileName, '.php'));
                    foreach ($functions as $functionIndex => $functionItem) {
                        if (preg_match('/^_/', $functionItem)) {
                            continue;
                        }
                        $script .= '    \'' . $functionItem . '\': function(jsonData, ajaxDoneCallBack) { ' . $beyond->prefix . 'apiAjax({\'url\': ' . $beyond->prefix . 'apiAjaxHandlerUrl, \'data\': { \'class\': \'' . basename($pluginDir) . '_' . basename($classFileName, '.php') . '\', \'call\': \'' . $functionItem . '\', \'data\': JSON.stringify(jsonData) } }, ajaxDoneCallBack); }, ' . PHP_EOL;
                    }

                    // End class
                    $script .= '  },' . PHP_EOL . PHP_EOL;
                }
            } catch (Exception $e) {
                print '// ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    // Enumerate site classes
    foreach (glob(__DIR__ . '/config/siteClasses/*.php') as $classFileName) {
        try {
            $error = true;
            try {
                include_once __DIR__ . '/config/siteClasses/' . basename($classFileName);
                $error = false;
            } catch (\Throwable $e) {
                print '// ' . $e->getMessage() . PHP_EOL;
            }
            if (!$error) {
                // Begin class
                $script .= '  \'' . basename($classFileName, '.php') . '\': {' . PHP_EOL;
                // Functions
                $functions = get_class_methods(basename($pluginDir) . '_' . basename($classFileName, '.php'));
                foreach ($functions as $functionIndex => $functionItem) {
                    if (preg_match('/^_/', $functionItem)) {
                        continue;
                    }
                    $script .= '    \'' . $functionItem . '\': function(jsonData, ajaxDoneCallBack) { ' . $beyond->prefix . 'apiAjax({\'url\': ' . $beyond->prefix . 'apiAjaxHandlerUrl, \'data\': { \'class\': \'' . basename($pluginDir) . '_' . basename($classFileName, '.php') . '\', \'call\': \'' . $functionItem . '\', \'data\': JSON.stringify(jsonData) } }, ajaxDoneCallBack); }, ' . PHP_EOL;
                }
                // End class
                $script .= '  },' . PHP_EOL . PHP_EOL;
            }
        } catch (Exception $e) {
            print '// ' . $e->getMessage() . PHP_EOL;
        }
    }
    unset($classFileName);

    // End API
    $script .= '};' . PHP_EOL;

    // -----------------------------------------------------------------------------------------------------------------

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

$exceptionArray = $beyond->exceptionHandler->arr();
if ($exceptionArray === false) {
    print $script;
} else {
    foreach ($exceptionArray as $exceptionIndex => $exceptionItem) {
        print 'console.log("API exception message: ' . addslashes($exceptionItem['message']) . '");' . PHP_EOL;
    }
    unset($exceptionItem);
    unset($exceptionIndex);
}
unset($exceptionArray);
unset($script);