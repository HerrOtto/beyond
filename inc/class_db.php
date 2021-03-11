<?php

/**
 * Handle database connections (configured within database.json)
 * @author     Tim David Saxen <info@netzmal.de>
 */
class db
{

    private $config;
    public $prefix;
    public $databases = array();
    public dbBaseClass $defaultDatabase;

    /**
     * Constructor
     * @param object $config Pointer to configuration object
     */
    function __construct(&$config, $prefix)
    {
        // Store paramters to class variables
        $this->config = $config;
        $this->prefix = $prefix;

        // Enumerate databases in configuration
        $databases = $this->config->get('database', 'items');
        foreach ($databases as $databaseIndex => $databaseItem) {

            // Connect to database
            if ($databaseItem['kind'] === 'mysql') {
                $this->databases[$databaseIndex] = new dbMySql(
                    $databaseItem['host'],
                    $databaseItem['port'],
                    $databaseItem['user'],
                    $databaseItem['pass'],
                    $databaseItem['base']
                );
            } else if ($databaseItem['kind'] === 'sqlite3') {
                $this->databases[$databaseIndex] = new dbSqlite3(
                    $databaseItem['file'],
                    $databaseItem['busyTimeoutMS']
                );
            }

        }

        // Check base tables
        $this->defaultDatabase = &$this->databases[$this->config->get('database', 'defaultDatabase')];

    }

}