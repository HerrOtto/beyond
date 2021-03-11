<?php

/**
 * Incuded on all pages just before closing site element
 */

try {

    // ...

} catch (Exception $e) {
    $exceptionHandler->add($e);
}

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/endSite.php')) {
        try {
            require_once $pluginDir . '/endSite.php';
        } catch (Exception $e) {
            $exceptionHandler->add($e);
        }
    }
}
