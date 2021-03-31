<?php

/**
 * Header script for all pages
 */

try {

    // Website config
    print '<meta charset="UTF-8" />' . PHP_EOL;

    // jQuery
    print '<script src="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/jquery-3.5.1/jquery.min.js"></script>' . PHP_EOL;

    // Bootstrap
    print '<link rel="stylesheet" href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/bootstrap-4.0.0/css/bootstrap.css" />' . PHP_EOL;
    print '<script src="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/bootstrap-4.0.0/js/bootstrap.bundle.js"></script>' . PHP_EOL;

    // Font awesome
    print '<link href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/fontawesome-free-5.15.2/css/all.min.css" rel="stylesheet" />' . PHP_EOL;
    print '<script src="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/fontawesome-free-5.15.2/js/fontawesome.js" ></script>' . PHP_EOL;

    // Startbootstrap template
    print '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />' . PHP_EOL;
    print '<link href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/assets/startbootstrap-6.0.2/dist/css/styles.css" rel="stylesheet" />' . PHP_EOL;

    // API
    print '<script src="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/base.php?nocache=' . urlencode(microtime(true) . bin2hex(random_bytes(10))) . '"></script>' . PHP_EOL;

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/head.php')) {
        try {
            require_once $pluginDir . '/head.php';
        } catch (Exception $e) {
            $beyond->exceptionHandler->add($e);
        }
    }
}
unset($pluginDir);

?>