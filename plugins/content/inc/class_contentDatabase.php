<?php

class contentDatabase
{

    private $prefix; // string

    /**
     * Constructor
     * @param string $prefix Prefix for this instance of beyond
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /*
     * Initialize plugin database
     * @param dbBaseClass $database Pointer to desired database object
     */
    public function init($database)
    {
        // Check the table tableVersionInfo holding the table versions
        if (!$database->tableExists($this->prefix . 'content_tableVersionInfo')) {
            $database->tableCreate(
                $this->prefix . 'content_tableVersionInfo',
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
        $query = $database->select($this->prefix . 'content_tableVersionInfo', array('tableName', 'tableVersion'), array());
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'content_tableVersionInfo]');
        }
        $tableVersions = array();
        while ($row = $query->fetch()) {
            $tableVersions[$row['tableName']] = intval($row['tableVersion']);
        }

        // Init "settings" table
        if (!array_key_exists($this->prefix . 'content_settings', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'content_settings',
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
                $this->prefix . 'content_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'content_settings',
                    'tableVersion' => 1
                )
            );
        }

        // Init "data" table
        if (!array_key_exists($this->prefix . 'content_data', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'content_data',
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
                $this->prefix . 'content_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'content_data',
                    'tableVersion' => 1
                )
            );
        }

    }

    /*
     * Drop plugin database
     * @param dbBaseClass $database Pointer to desired database object
     */
    public function drop($database)
    {
        $database->tableDrop($this->prefix . 'content_tableVersionInfo');
        $database->tableDrop($this->prefix . 'content_settings');
        $database->tableDrop($this->prefix . 'content_data');
    }

}