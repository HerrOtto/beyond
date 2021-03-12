<?php

/**
 * Handle database connections (configured within database.json)
 * @author     Tim David Saxen <info@netzmal.de>
 */
class beyondDatabaseConnection
{

    private beyondConfig $config;
    public string $prefix;
    public array $databases = array();
    public beyondDatabaseDriver $defaultDatabase;

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
                $this->databases[$databaseIndex] = new beyondDatabaseDriverMySql(
                    $databaseItem['host'],
                    $databaseItem['port'],
                    $databaseItem['user'],
                    $databaseItem['pass'],
                    $databaseItem['base']
                );
            } else if ($databaseItem['kind'] === 'sqlite3') {
                $this->databases[$databaseIndex] = new beyondDatabaseDriverSqlite3(
                    $databaseItem['file'],
                    intval($databaseItem['busyTimeoutMS'])
                );
            }

        }

        // Check base tables
        $this->defaultDatabase = &$this->databases[$this->config->get('database', 'defaultDatabase')];

    }

}