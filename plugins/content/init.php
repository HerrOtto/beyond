<?php

// Called from: ../../inc/init.php

$beyond->plugins->content = new stdClass();

// Includes
require_once __DIR__ . '/inc/class_contentDatabase.php';
require_once __DIR__ . '/inc/class_contentHandler.php';

/**
 * Create or update database
 */

try {

    // Get configured database from plugin configuration
    $configJson = file_get_contents(__DIR__ . '/../../config/content_settings.json');
    $configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);
    if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $beyond->db->databases))) {
        $database = $beyond->db->databases[$configObj->database];
    } else {
        $database = $beyond->db->defaultDatabase;
    }

    // Initialize Database
    $contentDatabase = new contentDatabase($beyond->prefix);
    $contentDatabase->init($database);

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

// Wrapper functions to simply access content handler
$beyond->content = new contentHandler(
    $_SESSION[$beyond->prefix . 'data']['language'],
    $beyond->prefix,
    $database,
    $beyond->tools,
    $beyond->config
);

