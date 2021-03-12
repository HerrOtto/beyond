<?php

/**
 * Incuded on all pages just before closing site element
 */

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/endSite.php')) {
        try {
            require_once $pluginDir . '/endSite.php';
        } catch (Exception $e) {
            $beyond->exceptionHandler->add($e);
        }
    }
}
unset($pluginDir);
