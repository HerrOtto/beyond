<?php

/**
 * Incuded on all pages directly after opening body tag
 */

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/begin.php')) {
        try {
            require_once $pluginDir . '/begin.php';
        } catch (Exception $e) {
            $beyond->exceptionHandler->add($e);
        }
    }
}
unset($pluginDir);

?>
