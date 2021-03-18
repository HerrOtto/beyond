<?php

// Called from: ../../inc/init.php

$beyond->plugins->blocks = new stdClass();

// Includes
include_once __DIR__ . '/inc/class_blocksDatabase.php';
include_once __DIR__ . '/inc/class_blocksHandler.php';

/**
 * Create or update database
 */

try {

    // Get configured database from plugin configuration
    $configJson = file_get_contents(__DIR__ . '/../../config/blocks_settings.json');
    $configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);
    if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $beyond->db->databases))) {
        $database = $beyond->db->databases[$configObj->database];
    } else {
        $database = $beyond->db->defaultDatabase;
    }

    // Initialize Database
    $blocksDatabase = new blocksDatabase($beyond->prefix);
    $blocksDatabase->init($database);

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

// Wrapper functions to simply access content handler
$beyond->blocks = new blocksHandler(
    $_SESSION[$beyond->prefix . 'data']['language'],
    $beyond->prefix,
    $database,
    $beyond->tools,
    $beyond->config
);
