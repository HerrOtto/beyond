<?php

/**
 * Handle database connections (configured within database.json)
 * @author     Tim David Saxen <info@netzmal.de>
 */
class beyondDatabaseConnection
{

    private $config; // beyondConfig
    public $prefix; // string
    public $databases = array(); // array
    public $defaultDatabase; // beyondDatabaseDriver

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

    /**
     * Move table from one database to another
     * @param object $dropFromTable Drop source table on success
     * @param object $fromDatabase Pointer to source database
     * @param object $toDatabase Pointer to destination database
     * @param object $fromtableName Table name on source database
     * @param object $toTableName Table name on destination database if not assigned or "false" the source name will be assigned
     */
    public function moveTable($dropFromTable, $fromDatabase, $toDatabase, $fromtableName, $toTableName = false)
    {

        // Check parameters
        if ($toTableName === false) {
            $toTableName = $fromtableName;
        }

        // Get table information
        $fieldListArray = $fromDatabase->tableInfo(
            $fromtableName,
            array(
                '*',
            ),
            array()
        );
        if ($fieldListArray === false) {
            throw new Exception('Can not query table information [' . $fromtableName . ']');
        }

        // Create new table
        $query = $toDatabase->tableCreate(
            $toTableName === false ? $fromtableName : $toTableName,
            $fieldListArray
        );
        if ($query === false) {
            throw new Exception('Can not create table [' . $fromtableName . ']');
        }

        // Load data from current database
        try {

            $query = $fromDatabase->select(
                $fromtableName,
                array(
                    '*',
                ),
                array()
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $fromtableName . ']');
            }

            // Store data in memory
            while ($row = $query->fetch()) {

                // Write data to new database
                $queryInsert = $toDatabase->insert(
                    $toTableName,
                    $row
                );
                if ($queryInsert === false) {
                    throw new Exception('Can not insert into table [' . $toTableName . ']');
                }

            }

        } catch (Exception $e) {

            // Drop destination table on failure

            $exception = $e->getMessage();

            $query = $fromDatabase->tableDrop(
                $toTableName
            );
            if ($query === false) {
                throw new Exception('Can not drop table [' . $toTableName . ']');
            }

            throw new Exception($exception);

        }

        // Drop table
        if ($dropFromTable) {
            $query = $fromDatabase->tableDrop(
                $fromtableName
            );
            if ($query === false) {
                throw new Exception('Can not drop table [' . $fromtableName . ']');
            }
        }

    }

}