<?php

// Called from: ../../inc/init.php
$beyond->plugins->seo = new stdClass();

// Includes
include_once __DIR__ . '/inc/class_seoDatabase.php';
include_once __DIR__ . '/inc/class_seoHandler.php';

/**
 * Create or update database
 */

try {

    // Get configured database from plugin configuration
    $configJson = file_get_contents(__DIR__ . '/../../config/seo_settings.json');
    $configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);
    if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $beyond->db->databases))) {
        $database = $beyond->db->databases[$configObj->database];
    } else {
        $database = $beyond->db->defaultDatabase;
    }

    // Initialize Database
    $seoDatabase = new seoDatabase($beyond->prefix);
    $seoDatabase->init($database);

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

// Wrapper functions to simply access content handler
$beyond->seo = new seoHandler(
    $_SESSION[$beyond->prefix . 'data']['language'],
    $beyond->prefix,
    $database,
    $beyond->tools,
    $beyond->config
);
