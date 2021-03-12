<?php

/**
 * Incuded on all pages just before closing body tag
 */

try {

    print '<script src="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/startbootstrap-6.0.2/dist/js/scripts.js"></script>' . PHP_EOL;

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

// Output exception messages
$exceptions = $beyond->exceptionHandler->html();
if ($exceptions !== false) {
    print '<div id="exceptions">' . $exceptions . '</div>';
}
unset($exceptions);

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/end.php')) {
        try {
            require_once $pluginDir . '/end.php';
        } catch (Exception $e) {
            $beyond->exceptionHandler->add($e);
        }
    }
}
unset($pluginDir);
