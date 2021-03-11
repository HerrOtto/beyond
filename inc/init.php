<?php

/**
 * Initial script for all pages
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/*
 * Initialize global variables
 */

foreach (glob(__DIR__ . '/class_*.php') as $classFileName) {
    require_once $classFileName;
}

$config = new config();
$exceptionHandler = new exceptionHandler($config);
$variable = new variable();

/*
 * Startup
 */

try {

    // Configure PHP
    error_reporting($config->get('base', 'php.errorReporting'));
    date_default_timezone_set($config->get('base', 'php.timeZone'));
    ini_set('default_charset', 'UTF-8');

    // Get cookie, field, session and db prefix
    $prefix = $config->get('base', 'site.prefix', 'nm_');

    // Init global used functions class
    $tools = new tools($prefix);

    // Start session
    if (session_set_cookie_params(
            $config->get('base', 'site.session.lifeTimeSec', 86400),
            $config->get('base', 'site.session.path', '/'),
            $config->get('base', 'site.session.domain', null),
    ) === false) {
        throw new Exception('Can not configure session cookie');
    }
    $sid = $variable->get('sid', '');
    if ($sid !== '') {
        session_id($sid);
    }
    session_name($config->get('base', 'site.session.name', 'nmSession'));
    if (session_start() === false) {
        throw new Exception('Can not start session');
    }
    if (!array_key_exists($prefix . 'data', $_SESSION)) {
        $_SESSION[$prefix . 'data'] = array();
    }

    // Get language configuration
    $languages = $config->get('base', 'languages', false);
    if ($languages === false) {
        $languages = array(
            'default' => 'English'
        );
    }
    if (!array_key_exists('language', $_SESSION[$prefix . 'data'])) {
        $_SESSION[$prefix . 'data']['language'] = 'default';
    }

    // Initialize Database connections
    $db = new db($config, $prefix);
} catch (Exception $e) {
    $exceptionHandler->add($e);
}

/*
 * Initialize internal database tables
 */

try {

    // Check the table tableVersionInfo holding the table versions
    if (!$db->defaultDatabase->tableExists($prefix . 'tableVersionInfo')) {
        $db->defaultDatabase->tableCreate(
            $prefix . 'tableVersionInfo',
            array(
                'tableName' => array(
                    'kind' => 'string',
                    'index' => 'primary'
                ),
                'tableVersion' => array(
                    'kind' => 'number'
                )
            )
        );
    }

    // Store table versions to key/value array
    $query = $db->defaultDatabase->select($prefix . 'tableVersionInfo', array('tableName', 'tableVersion'), array());
    if ($query === false) {
        throw new Exception('Can not query table [' . $prefix . 'tableVersionInfo]');
    }
    $tableVersions = array();
    while ($row = $query->fetch()) {
        $tableVersions[$row['tableName']] = intval($row['tableVersion']);
    }

    // Init "users" table
    if (!array_key_exists($prefix . 'users', $tableVersions)) {
        $db->defaultDatabase->tableCreate(
            $prefix . 'users',
            array(
                'userName' => array(
                    'kind' => 'string',
                    'index' => 'primary'
                ),
                'password' => array(
                    'kind' => 'string'
                ),
                'roles' => array(
                    'kind' => 'string'
                )
            )
        );
        $db->defaultDatabase->insert(
            $prefix . 'tableVersionInfo',
            array(
                'tableName' => $prefix . 'users',
                'tableVersion' => 1
            )
        );
        $db->defaultDatabase->insert(
            $prefix . 'users',
            array(
                'userName' => 'admin',
                'password' => password_hash('password', PASSWORD_DEFAULT, array('cost' => 11)),
                'roles' => 'admin'
            )
        );
    }

} catch (Exception $e) {
    $exceptionHandler->add($e);
}

/*
 * Include plugin initialization
 */

foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/init.php')) {
        try {
            require_once $pluginDir . '/init.php';
        } catch (Exception $e) {
            $exceptionHandler->add($e);
        }
    }
}
