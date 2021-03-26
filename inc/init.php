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

foreach (glob(__DIR__ . '/classes/class_*.php') as $classFileName) {
    require_once $classFileName;
}
unset($classFileName);

$beyond = new stdClass();
$beyond->config = new beyondConfig();
$beyond->exceptionHandler = new beyondExceptionHandler($beyond->config);
$beyond->variable = new beyondVariable();
$beyond->plugins = new stdClass();

/*
 * Startup
 */

try {

    // Configure PHP
    error_reporting($beyond->config->get('base', 'php.errorReporting'));
    date_default_timezone_set($beyond->config->get('base', 'php.timeZone'));
    ini_set('default_charset', 'UTF-8');

    // Get cookie, field, session and db prefix
    $beyond->prefix = $beyond->config->get('base', 'site.prefix', 'nm_');

    // Init global used functions class
    $beyond->tools = new beyondTools($beyond->prefix, $beyond->config);

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        if (session_set_cookie_params(
                $beyond->config->get('base', 'site.session.lifeTimeSec', 86400),
                $beyond->config->get('base', 'site.session.path', '/'),
                $beyond->config->get('base', 'site.session.domain', null),
            ) === false) {
            throw new Exception('Can not configure session cookie');
        }
        $beyond->sid = $beyond->variable->get('beyondSid', '');
        if ($beyond->sid !== '') {
            session_id($beyond->sid);
        }
        session_name($beyond->config->get('base', 'site.session.name', 'nmSession'));
        if (session_start() === false) {
            throw new Exception('Can not start session');
        }
    }
    $beyond->sid = session_id();
    if (!array_key_exists($beyond->prefix . 'data', $_SESSION)) {
        $_SESSION[$beyond->prefix . 'data'] = array();
    }

    // Get language configuration
    $beyond->languages = $beyond->config->get('base', 'languages', false);
    if ($beyond->languages === false) {
        $beyond->languages = array(
            'default' => 'English'
        );
    }
    if (!array_key_exists('language', $_SESSION[$beyond->prefix . 'data'])) {
       $_SESSION[$beyond->prefix . 'data']['language'] = 'default';
    }

    // Initialize Database connections
    $beyond->db = new beyondDatabaseConnection($beyond->config, $beyond->prefix);
} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
}

/*
 * Initialize internal database tables
 */

try {

    // Check the table tableVersionInfo holding the table versions
    if (!$beyond->db->defaultDatabase->tableExists($beyond->prefix . 'tableVersionInfo')) {
        $beyond->db->defaultDatabase->tableCreate(
            $beyond->prefix . 'tableVersionInfo',
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
    $query = $beyond->db->defaultDatabase->select($beyond->prefix . 'tableVersionInfo', array('tableName', 'tableVersion'), array());
    if ($query === false) {
        throw new Exception('Can not query table [' . $beyond->prefix . 'tableVersionInfo]');
    }
    $tableVersions = array();
    while ($row = $query->fetch()) {
        $tableVersions[$row['tableName']] = intval($row['tableVersion']);
    }
    unset($query);

    // Init "users" table
    if (!array_key_exists($beyond->prefix . 'users', $tableVersions)) {
        $beyond->db->defaultDatabase->tableCreate(
            $beyond->prefix . 'users',
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
        $beyond->db->defaultDatabase->insert(
            $beyond->prefix . 'tableVersionInfo',
            array(
                'tableName' => $beyond->prefix . 'users',
                'tableVersion' => 1
            )
        );
        $beyond->db->defaultDatabase->insert(
            $beyond->prefix . 'users',
            array(
                'userName' => 'admin',
                'password' => password_hash('password', PASSWORD_DEFAULT, array('cost' => 11)),
                'roles' => 'admin'
            )
        );
    }
    unset($tableVersions);

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);
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
            $beyond->exceptionHandler->add($e);
        }
    }
}
unset($pluginDir);
