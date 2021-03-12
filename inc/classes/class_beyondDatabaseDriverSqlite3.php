<?php

class beyondDatabaseDriverSqlite3 extends beyondDatabaseDriver
{

    // Connection settings
    private string $file = "";
    private int $busyTimeoutMS;

    // Constructor
    public function __construct($file, $busyTimeoutMS)
    {
        // Parameter ernehmen
        $this->file = __DIR__ . '/../../db/' . basename($file);
        $this->busyTimeoutMS = intval($busyTimeoutMS);

        // Connect
        if (!class_exists('SQLite3')) {
            throw new Exception('Could not connect to sqlite3 database [' . basename($this->file) . '] error message [SQLite3 extension not loaded]');
        }

        try {
            $this->connection = new SQLite3($this->file, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        } catch (Exception $e) {
            throw new Exception('Could not connect to sqlite3 database [' . basename($this->file) . '] error message [' . $e->getMessage() . ']');
        }

        // Init connection
        if ($this->connection->busyTimeout($busyTimeoutMS) === false) {
            throw new Exception('Could not change busy timeout on database [' . basename($this->file) . ']');
        }

    }

    // Destructor
    public function __destruct()
    {
        // Disconnect
    }

    // Escape String
    public function escape($string)
    {
        return $this->connection->escapeString($string);
    }

    // Fetch as Array
    public function internalFetch($queryResult)
    {
        return $queryResult->fetchArray(SQLITE3_ASSOC);
    }

    // Query
    public function query($sql)
    {
        $queryResult = $this->connection->query($sql);
        if ($queryResult === false) {
            return false;
        } else {
            return new dbQueryClass($sql, $queryResult, $this);
        }
    }

    // Last insert ID
    public function insertId()
    {
        return $this->connection->lastInsertRowID;
    }

    // Parse WHERE array
    protected function internalParseWhere($whereArray)
    {
        $where = "";
        foreach ($whereArray as $whereIndex => $whereItem) {
            //$whereItem = array(
            //    "field != field",
            //    "field != 'value'",
            //    "field = field",
            //    "field = 'value'",
            //    "field > field",
            //    "field > 'value'",
            //    "field < field",
            //    "field < 'value'",
            //    "field LIKE field",
            //    "field LIKE '%value%'",
            //    "field NOT LIKE field",
            //    "field NOT LIKE '%value%'"
            //);
            if (preg_match('/([^\s]*)\s*(!=|=|<|>|like|not like)\s*(\w+|\'[^\']*\')/i', $whereItem, $matches)) { //
                $where .= ($where === '' ? 'WHERE ' : 'AND ') .
                    $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
            } else {
                throw new Exception('Can not parse WHERE [' . $whereItem . '] condition');
            }
        }
        return $where;
    }

    // Simple select from table
    public function select($tableName, $fieldArray, $whereArray, $offset = false, $limit = false)
    {
        $sql = 'SELECT ' . implode(',', $fieldArray) . ' ' .
            'FROM ' . $this->escape($tableName) . ' ' .
            $this->internalParseWhere($whereArray);

        if (($offset !== false) and ($limit !== false)) {
            $sql .= ' LIMIT ' . intval($limit) . ' OFFSET  ' . intval($offset);
        }

        return $this->query($sql);
    }

    // Count rows of query
    public function count($tableName, $whereArray)
    {
        $result = false;

        $sql = 'SELECT COUNT(*) AS rowCount  FROM ' . $this->escape($tableName) . ' ' . $this->internalParseWhere($whereArray);
        $query = $this->query($sql);
        if (($query !== false) && ($row = $query->fetch())) {
            $result = $row['rowCount'];
        }

        return $result;
    }

    // Simple insert into table
    public function insert($tableName, $fieldArray)
    {
        $columns = "";
        $values = "";
        foreach ($fieldArray as $column => $value) {
            $columns .= ($columns === '' ? '' : ', ') . $this->escape($column);
            if (is_numeric($value)) {
                $values .= ($values === '' ? '' : ', ') . $this->escape($value);
            } else {
                $values .= ($values === '' ? '' : ', ') . '\'' . $this->escape($value) . '\'';
            }
        }

        $sql = 'INSERT INTO ' . $this->escape($tableName) . ' (' . $columns . ') VALUES (' . $values . ')';
        return $this->query($sql) === false ? $this->connection->lastErrorMsg() : true;
    }

    // Simple update table
    public function update($tableName, $fieldArray, $whereArray)
    {
        $fields = "";
        foreach ($fieldArray as $column => $value) {
            if (is_numeric($value)) {
                $value = $this->escape($value);
            } else {
                $value = '\'' . $this->escape($value) . '\'';
            }
            $fields .= ($fields === '' ? '' : ', ') . $this->escape($column) . ' = ' . $value;
        }

        $sql = 'UPDATE ' . $this->escape($tableName) . ' SET ' . $fields . ' ' . $this->internalParseWhere($whereArray);

        return $this->query($sql) === false ? $this->connection->lastErrorMsg() : true;
    }

    // Simple delete table
    public function delete($tableName, $whereArray)
    {
        $sql = 'DELETE FROM ' . $this->escape($tableName) . ' ' . $this->internalParseWhere($whereArray);

        return $this->query($sql) === false ? $this->connection->lastErrorMsg() : true;
    }


    // Check if table exists
    public function tableExists($tableName)
    {
        $result = false;
        $sql = 'SELECT COUNT(*) AS rowsCount FROM sqlite_master WHERE type=\'table\' AND name=\'' . $this->escape($tableName) . '\'';
        $query = $this->query($sql);
        if (($query !== false) && ($row = $query->fetch())) {
            if ($row['rowsCount'] > 0) {
                $result = true;
            }
        }
        return $result;
    }

    private function fieldToSQL($field)
    {
        $result = '';

        // kind: string, longText, integer, decimal - default "string"
        // index: primary, unique, ""           - default ""
        // null: true/false                     - default "false"
        // default: number or text              - default n/a

        if (in_array(strtolower($field['kind']), $this->textNames)) {
            $result .= " TEXT";
        } else if (in_array(strtolower($field['kind']), $this->integerNames)) {
            $result .= " INTEGER";
        } else if (in_array(strtolower($field['kind']), $this->decimalNames)) {
            $result .= " REAL";
        } else {
            $result .= " TEXT";
        }

        if ((!array_key_exists('null', $field)) && (!$field['null'])) {
            $result .= " NOT NULL";
        }

        // TODO: AUTO_INCREMENT

        if (array_key_exists('default', $field)) {
            if ($field['default'] == 'NULL') {
                $result .= ' DEFAULT NULL';
            } else if (in_array(strtolower($field['kind']), $this->decimalNames)) {
                $result .= ' DEFAULT ' . floatval($field['default']);
            } else if (in_array(strtolower($field['kind']), $this->integerNames)) {
                $result .= ' DEFAULT ' . intval($field['default']);
            } else {
                $result .= ' DEFAULT \'' . $this->escape($field['default']) . '\'';
            }
        }

        if (array_key_exists('index', $field)) {
            if ($field['index'] === 'primary') {
                $result .= " PRIMARY KEY";
            } else if ($field['index'] === 'unique') {
                $result .= " UNIQUE";
            }
        }

        return $result;
    }

    // Create table
    public function tableCreate($tableName, $fieldListArray)
    {
        // $fieldListArray = array(
        //   'field1' => array(),
        //   'field2' => array()
        // );

        $fields = "";
        foreach ($fieldListArray as $fieldIndex => $fieldItem) {
            if (is_object($fieldItem)) {
                $fieldItem = (array)$fieldItem;
            }
            $fields .= ($fields === '' ? '' : ', ') . $this->escape($fieldIndex);
            $fields .= $this->fieldToSQL($fieldItem);
        }

        if ($fields === '') {
            return 'No fileds defined';
        } else {
            $sql = 'CREATE TABLE ' . $tableName . ' (' .
                $fields .
                ')';
        }
        return $this->query($sql) === false ? $this->connection->lastErrorMsg() : true;
    }

    // Add a column to table
    public function tableColumnAdd($tableName, $fieldName, $field)
    {

        // In SQLite you are not allowed to add PRIMARY KEY after table creation
        // Workaround:
        // * Create new table (CREATE TABLE)
        // * Copy old data to new table (INSERT SELECT)
        // * Rename old table to temp table (ALTER TABLE)
        // * Rename new table to old table (ALTER TABLE)
        // * Drop temp table (DROP TABLE)

        // Fetch field list
        $fieldListArray = $this->tableInfo($tableName);
        if (count($fieldListArray) <= 0) {
            return 'Error fetching table information';
        }
        if (array_key_exists($fieldName, $fieldListArray)) {
            return 'Field name already exists';
        }

        // We need a temporary table name
        $temp_table = $tableName . '_' . bin2hex(random_bytes(10));

        // Add field to list
        $fieldListArray[$fieldName] = $field;

        // Build CREATE query for temp table
        $fields = "";
        foreach ($fieldListArray as $fieldIndex => $fieldItem) {
            if (is_object($fieldItem)) {
                $fieldItem = (array)$fieldItem;
            }
            $fields .= ($fields === '' ? '' : ', ') . $this->escape($fieldIndex);
            $fields .= $this->fieldToSQL($fieldItem);
        }
        if ($fields === '') {
            return 'No fileds defined';
        }
        $sqlCreate = 'CREATE TABLE ' . $temp_table . ' (' .
            $fields .
            ')';

        // Build INSERT query (copy data to temp table)
        $fields = "";
        foreach ($fieldListArray as $fieldIndex => $fieldItem) {
            if (is_object($fieldItem)) {
                $fieldItem = (array)$fieldItem;
            }
            if ($fieldIndex === $fieldName) {
                continue; // Ignore new field
            }
            $fields .= ($fields === '' ? '' : ', ') . $this->escape($fieldIndex);
        }
        if ($fields === '') {
            return 'No fileds defined';
        }
        $sqlCopy = 'INSERT INTO ' . $temp_table . ' (' . $fields . ') SELECT ' . $fields . ' FROM ' . $tableName;

        // Build RENAME and DROP query

        if ($this->query($sqlCreate) === false) {
            return 'Can not create temporary table [' . $temp_table . ']: ' . $this->connection->lastErrorMsg();
        }

        if ($this->query($sqlCopy) === false) {
            $this->query('DROP TABLE ' . $temp_table); // cleanup
            return 'Can not copy data to temporary table [' . $temp_table . ']: ' . $this->connection->lastErrorMsg();
        }

        if ($this->query('DROP TABLE ' . $tableName) === false) {
            $this->query('DROP TABLE ' . $temp_table); // cleanup
            return 'Can not drop original table [' . $tableName . ']: ' . $this->connection->lastErrorMsg();
        }

        if ($this->query('ALTER TABLE ' . $temp_table . ' RENAME TO ' . $tableName) === false) {
            // Can not cleanup here
            return 'Can not rename temporary table [' . $temp_table . '] to original table [' . $tableName . ']: ' . $this->connection->lastErrorMsg();
        }

        // Done

        return true;
    }

    // Remove a column from table
    public function tableColumnDrop($tableName, $fieldName)
    {

        // In SQLite you are not allowed to remove column after table creation
        // Workaround:
        // * Create new table (CREATE TABLE)
        // * Copy old data to new table (INSERT SELECT)
        // * Rename old table to temp table (ALTER TABLE)
        // * Rename new table to old table (ALTER TABLE)
        // * Drop temp table (DROP TABLE)

        // Fetch field list
        $fieldListArray = $this->tableInfo($tableName);
        if (count($fieldListArray) <= 0) {
            return 'Error fetching table information';
        }
        if (!array_key_exists($fieldName, $fieldListArray)) {
            return 'Field name does not exist';
        }

        // We need a temporary table name
        $temp_table = $tableName . '_' . bin2hex(random_bytes(10));

        // Build CREATE query for temp table
        $fields = "";
        foreach ($fieldListArray as $fieldIndex => $fieldItem) {
            if (is_object($fieldItem)) {
                $fieldItem = (array)$fieldItem;
            }
            if ($fieldIndex === $fieldName) {
                continue; // Ignore new field
            }
            $fields .= ($fields === '' ? '' : ', ') . $this->escape($fieldIndex);
            $fields .= $this->fieldToSQL($fieldItem);
        }
        if ($fields === '') {
            return 'No fileds defined';
        }
        $sqlCreate = 'CREATE TABLE ' . $temp_table . ' (' .
            $fields .
            ')';

        // Build INSERT query (copy data to temp table)
        $fields = "";
        foreach ($fieldListArray as $fieldIndex => $fieldItem) {
            if (is_object($fieldItem)) {
                $fieldItem = (array)$fieldItem;
            }
            if ($fieldIndex === $fieldName) {
                continue; // Ignore new field
            }
            $fields .= ($fields === '' ? '' : ', ') . $this->escape($fieldIndex);
        }
        if ($fields === '') {
            return 'No fileds defined';
        }
        $sqlCopy = 'INSERT INTO ' . $temp_table . ' (' . $fields . ') SELECT ' . $fields . ' FROM ' . $tableName;

        // Build RENAME and DROP query

        if ($this->query($sqlCreate) === false) {
            return 'Can not create temporary table [' . $temp_table . ']: ' . $this->connection->lastErrorMsg();
        }

        if ($this->query($sqlCopy) === false) {
            $this->query('DROP TABLE ' . $temp_table); // cleanup
            return 'Can not copy data to temporary table [' . $temp_table . ']: ' . $this->connection->lastErrorMsg();
        }

        if ($this->query('DROP TABLE ' . $tableName) === false) {
            $this->query('DROP TABLE ' . $temp_table); // cleanup
            return 'Can not drop original table [' . $tableName . ']: ' . $this->connection->lastErrorMsg();
        }

        if ($this->query('ALTER TABLE ' . $temp_table . ' RENAME TO ' . $tableName) === false) {
            // Can not cleanup here
            return 'Can not rename temporary table [' . $temp_table . '] to original table [' . $tableName . ']: ' . $this->connection->lastErrorMsg();
        }

        // Done

        return true;
    }

    // Fetch tables information
    public function tableInfo($tableName)
    {
        $fieldsArray = array();

        $sql = 'PRAGMA table_info(\'' . $this->escape($tableName) . '\')';
        $query = $this->query($sql);
        if ($query !== false) {
            while ($row = $query->fetch()) {

                // Map SQLite cell type to beyond cell type
                if ($row['type'] === 'TEXT') {
                    $type = $this->textNames[0];
                } else if ($row['type'] === 'INTEGER') {
                    $type = $this->integerNames[0];
                } else if ($row['type'] === 'REAL') {
                    $type = $this->decimalNames[0];
                } else {
                    $type = 'unknown:' . $row['type'];
                }

                // Add field to field list
                $fieldsArray[$row['name']] = array(
                    'kind' => $type,
                    'null' => ($row['notnull'] == 1),
                    'index' => ($row['pk'] == 1 ? 'primary' : ''), // Primary key
                    // AUTO_INCREMENT
                );

                // Default value
                if ($row['dflt_value'] != '') {
                    if (preg_match('/^[\'|"](.*)[\'|"]$/', $row['dflt_value'], $matches)) {
                        $fieldsArray[$row['name']]['default'] = $matches[1];
                    } else {
                        $fieldsArray[$row['name']]['default'] = $row['dflt_value'];
                    }
                }

            }
        }

        // Fetch all indexes
        $sql = 'PRAGMA index_list(\'' . $this->escape($tableName) . '\')';
        $query = $this->query($sql);
        if ($query !== false) {
            while ($row = $query->fetch()) {

                // If index is unique fetch name
                if ($row['unique'] === 1) {
                    $sqlIndex = 'PRAGMA index_info(\'' . $this->escape($row['name']) . '\')';
                    $queryIndex = $this->query($sqlIndex);
                    if ($queryIndex !== false) {
                        while ($rowIndex = $queryIndex->fetch()) {
                            if ($fieldsArray[$rowIndex['name']]['index'] !== 'primary') {
                                $fieldsArray[$rowIndex['name']]['index'] = 'unique';
                            }
                        }
                    }
                }

            }
        }

        return $fieldsArray;
    }

    // Drop table
    public function tableDrop($tableName)
    {
        $sql = 'DROP TABLE ' . $tableName;
        return $this->query($sql) === false ? $this->connection->lastErrorMsg() : true;
    }

    // Fetch list of tables
    public function tableList()
    {
        $result = array();
        $sql = 'SELECT `name` FROM sqlite_master WHERE type=\'table\' ORDER BY `name`';
        $query = $this->query($sql);
        if ($query !== false) {
            while ($row = $query->fetch()) {
                array_push($result, $row['name']);
            }
        }
        return $result;
    }

}
