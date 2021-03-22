<?php

class blocksDatabase
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
        if (!$database->tableExists($this->prefix . 'blocks_tableVersionInfo')) {
            $database->tableCreate(
                $this->prefix . 'blocks_tableVersionInfo',
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
        $query = $database->select($this->prefix . 'blocks_tableVersionInfo', array('tableName', 'tableVersion'), array());
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'blocks_tableVersionInfo]');
        }
        $tableVersions = array();
        while ($row = $query->fetch()) {
            $tableVersions[$row['tableName']] = intval($row['tableVersion']);
        }

        // Init "data" table
        if (!array_key_exists($this->prefix . 'blocks_data', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'blocks_data',
                array(
                    'name' => array(
                        'kind' => 'string',
                        'index' => 'primary'
                    ),
                    'kind' => array(
                        'kind' => 'string'
                    ),
                    'content' => array(
                        'kind' => 'longtext'
                    )
                )
            );
            $database->insert(
                $this->prefix . 'blocks_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'blocks_data',
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
        $database->tableDrop($this->prefix . 'blocks_tableVersionInfo');
        $database->tableDrop($this->prefix . 'blocks_data');
    }

}