<?php

/**
 * Incuded on all pages just before closing body tag
 */

try {

    print '<script src="' . $config->get('base', 'server.baseUrl') . '/beyond/assets/startbootstrap-6.0.2/dist/js/scripts.js"></script>' . PHP_EOL;

} catch (Exception $e) {
    $exceptionHandler->add($e);
}

// Output exception messages
$exceptions = $exceptionHandler->html();
if ($exceptions !== false) {
    print '<div id="exceptions">' . $exceptions . '</div>';
}

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/end.php')) {
        try {
            require_once $pluginDir . '/end.php';
        } catch (Exception $e) {
            $exceptionHandler->add($e);
        }
    }
}
