<?php

class mailDatabase
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
        if (!$database->tableExists($this->prefix . 'mail_tableVersionInfo')) {
            $database->tableCreate(
                $this->prefix . 'mail_tableVersionInfo',
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
        $query = $database->select($this->prefix . 'mail_tableVersionInfo', array('tableName', 'tableVersion'), array());
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'mail_tableVersionInfo]');
        }
        $tableVersions = array();
        while ($row = $query->fetch()) {
            $tableVersions[$row['tableName']] = intval($row['tableVersion']);
        }

        // Init "data" table
        //  if (!array_key_exists($this->prefix . 'mail_data', $tableVersions)) {
        $database->tableCreate(
            $this->prefix . 'mail_data',
            array(
                'id' => array(
                    'kind' => 'integer',
                    'index' => 'auto'
                ),
                'date' => array(
                    'kind' => 'string'
                ),
                'from' => array(
                    'kind' => 'string'
                ),
                'to' => array(
                    'kind' => 'string'
                ),
                'replyTo' => array(
                    'kind' => 'string'
                ),
                'bcc' => array(
                    'kind' => 'string'
                ),
                'subject' => array(
                    'kind' => 'string'
                ),
                'mail' => array(
                    'kind' => 'longtext'
                ),
                'kind' => array(
                    'kind' => 'string'
                )
            )
        );
        $database->insert(
            $this->prefix . 'mail_tableVersionInfo',
            array(
                'tableName' => $this->prefix . 'mail_data',
                'tableVersion' => 1
            )
        );
        // }

    }

    /*
     * Drop plugin database
     * @param dbBaseClass $database Pointer to desired database object
     */
    public function drop($database)
    {
        $database->tableDrop($this->prefix . 'mail_tableVersionInfo');
        $database->tableDrop($this->prefix . 'mail_data');
    }

}