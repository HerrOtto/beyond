<?php

// Called from: ../../inc/init.php

/*
 * Initialize plugin database
 */

try {

    // Get configured database
    $configJson = file_get_contents(__DIR__ . '/../../config/content_settings.json');
    $configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);

    if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $db->databases))) {
        $database = $db->databases[$configObj->database];
    } else {
        $database = $db->defaultDatabase;
    }


    // Check the table tableVersionInfo holding the table versions
    if (!$database->tableExists($prefix . 'content_tableVersionInfo')) {
        $database->tableCreate(
            $prefix . 'content_tableVersionInfo',
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
    $query = $database->select($prefix . 'content_tableVersionInfo', array('tableName', 'tableVersion'), array());
    if ($query === false) {
        throw new Exception('Can not query table [' . $prefix . 'content_tableVersionInfo]');
    }
    $tableVersions = array();
    while ($row = $query->fetch()) {
        $tableVersions[$row['tableName']] = intval($row['tableVersion']);
    }

    // Init "settings" table
    if (!array_key_exists($prefix . 'content_settings', $tableVersions)) {
        $database->tableCreate(
            $prefix . 'content_settings',
            array(
                'filePathName' => array(
                    'kind' => 'string',
                    'index' => 'primary'
                ),
                'configJson' => array(
                    'kind' => 'longtext'
                )
            )
        );
        $database->insert(
            $prefix . 'content_tableVersionInfo',
            array(
                'tableName' => $prefix . 'content_settings',
                'tableVersion' => 1
            )
        );
    }

    // Init "data" table
    if (!array_key_exists($prefix . 'content_data', $tableVersions)) {
        $database->tableCreate(
            $prefix . 'content_data',
            array(
                'filePathName' => array(
                    'kind' => 'string',
                    'index' => 'primary'
                ),
                'dataJson' => array(
                    'kind' => 'longtext'
                )
            )
        );
        $database->insert(
            $prefix . 'content_tableVersionInfo',
            array(
                'tableName' => $prefix . 'content_data',
                'tableVersion' => 1
            )
        );
    }

} catch (Exception $e) {
    $exceptionHandler->add($e);
}
