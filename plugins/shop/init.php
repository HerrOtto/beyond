<?php

// Called from: ../../inc/init.php

$beyond->plugins->shop = new stdClass();

// Includes
include_once __DIR__ . '/inc/class_shopDatabase.php';
include_once __DIR__ . '/inc/class_shopHandler.php';

/**
 * Create or update database
 */

try {

    // Get configured database from plugin configuration
    $configJson = file_get_contents(__DIR__ . '/../../config/shop_settings.json');
    $configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);
    if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $beyond->db->databases))) {
        $database = $beyond->db->databases[$configObj->database];
    } else {
        $database = $beyond->db->defaultDatabase;
    }

    // Initialize Database
    $shopDatabase = new shopDatabase($beyond->prefix);
    $shopDatabase->init($database);

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

// Wrapper functions to simply access content handler
$beyond->shop = new shopHandler(
    $_SESSION[$beyond->prefix . 'data']['language'],
    $beyond->prefix,
    $database,
    $beyond->tools,
    $beyond->config
);

// Initialize session variables
if (!array_key_exists('plugin_shop', $_SESSION[$beyond->prefix . 'data'])) {
    $_SESSION[$beyond->prefix . 'data']['plugin_shop'] = array(
    );
}
if (!array_key_exists('cart', $_SESSION[$beyond->prefix . 'data']['plugin_shop'])) {
    $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'] = array(
    );
}
if (!array_key_exists('coupon', $_SESSION[$beyond->prefix . 'data']['plugin_shop'])) {
    $_SESSION[$beyond->prefix . 'data']['plugin_shop']['coupon'] = false;
}


