<?php

class beyondDatabaseDriverMySql extends beyondDatabaseDriver
{

    // Connection settings
    private $host = ""; // string
    private $port = ""; // string
    private $user = ""; // string
    private $pass = ""; // string
    private $base = ""; // string

    // Constructor
    public function __construct($host, $port = 3389, $user, $pass, $base)
    {
        // Parameter ernehmen
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->base = $base;

        // Connect
        if (!class_exists('mysqli')) {
            throw new Exception('Could not connect to mysql database [' . basename($this->file) . '] error message [mysqli extension not loaded]');
        }
        $this->connection = new mysqli(
            $this->host, $this->user, $this->pass, $this->base, $this->port
        );
        if (!$this->connection) {
            throw new Exception('Could not connect to mysql database [' . $this->host . ':' . $this->port . '/' . $this->base . '] error message [' . $this->connection->connect_error . ']');
        }

        // Init connection
        if ($this->connection->query("SET CHARACTER SET 'utf8'") === false) {
            throw new Exception('Could not change character charset on database [' . $this->host . ':' . $this->port . '/' . $this->base . '] error [' . $this->connection->error . ']');
        }
        if ($this->connection->query("SET NAMES 'utf8'") === false) {
            throw new Exception('Could not change names charset on database [' . $this->host . ':' . $this->port . '/' . $this->base . ']');
        }

    }

    // Destructor
    public function __destruct()
    {
        if (is_resource($this->connection)) {
            mysqli_close($this->connection);
        }
    }

    // Escape String
    public function escape($string)
    {
        return $this->connection->real_escape_string($string);
    }

    // Fetch as Array
    public function internalFetch($queryResult)
    {
        return $queryResult->fetch_assoc();;
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

    // Get last insert ID
    public function insertId()
    {
        return $this->connection->insert_id;
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
    public function select($tableName, $fieldArray, $whereArray, $offset = false, $limit = false, $order = false)
    {
        $fields = '';
        foreach ($fieldArray as $field) {
            if ($field === '*') {
                $fields .= ($fields === '' ? '' : ', ') . '*';
            } else {
                $fields .= ($fields === '' ? '' : ', ') . '`' . $this->escape($field) . '`';
            }
        }

        $sql = 'SELECT ' . $fields . ' ' .
            'FROM ' . $this->escape($tableName) . ' ' .
            $this->internalParseWhere($whereArray);

        if ($order !== false) {
            $orderItem = explode(':', $order, 2);
            $orderItem[0] = '`' . $this->escape($orderItem[0]) . '`';
            if ((count($orderItem) > 1) && (strtolower($orderItem[1]) === 'desc')) {
                $orderItem[1] = 'DESC';
            } else {
                $orderItem[1] = '';
            }
            $sql .= ' ORDER BY ' . $orderItem[0] . ' ' . $orderItem[1];
        }

        if (($offset !== false) and ($limit !== false)) {
            $sql .= ' LIMIT ' . intval($offset) . ', ' . intval($limit);
        }

        return $this->query($sql);
    }

    // Count rows of query
    public function count($tableName, $whereArray)
    {
        $result = false;

        $sql = 'SELECT COUNT(*) AS rowCount FROM ' . $this->escape($tableName) . ' ' . $this->internalParseWhere($whereArray);
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
            $columns .= ($columns === '' ? '' : ', ') . '`' . $this->escape($column) . '`';
            if (is_numeric($value)) {
                $values .= ($values === '' ? '' : ', ') . $this->escape($value);
            } else {
                $values .= ($values === '' ? '' : ', ') . '\'' . $this->escape($value) . '\'';
            }
        }

        $sql = 'INSERT INTO ' . '`' . $this->escape($tableName) . '`' . ' (' . $columns . ') VALUES (' . $values . ')';
        return $this->query($sql) === false ? $this->connection->error : true;
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
            $fields .= ($fields === '' ? '' : ', ') . '`' . $this->escape($column) . '`' . ' = ' . $value;
        }

        $sql = 'UPDATE ' . $this->escape($tableName) . ' SET ' . $fields . ' ' . $this->internalParseWhere($whereArray);

        return $this->query($sql) === false ? $this->connection->error : true;
    }

    // Simple delete table
    public function delete($tableName, $whereArray)
    {
        $sql = 'DELETE FROM ' . '`' . $this->escape($tableName) . '`' . ' ' . $this->internalParseWhere($whereArray);
        return $this->query($sql) === false ? $this->connection->error : true;
    }

    // Check if table exists
    public function tableExists($tableName)
    {
        $result = false;
        $sql = 'SELECT COUNT(*) AS rowsCount FROM information_schema.tables WHERE table_schema=\'' . $this->escape($this->base) . '\' AND table_name=\'' . $this->escape($tableName) . '\'';
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
        // index: primary, unique, auto, ""     - default ""
        // null: true/false                     - default "false"
        // default: number or text              - default n/a

        $autoIncrement = false;
        if (array_key_exists('index', $field)) {
            if ($field['index'] === 'auto') {
                $autoIncrement = true;
            }
        }

        if ($autoIncrement) {
            $result .= ' INT';
        } else if (in_array(strtolower($field['kind']), $this->textNames)) {
            $result .= ' LONGTEXT';
        } else if (in_array(strtolower($field['kind']), $this->integerNames)) {
            $result .= ' INT';
        } else if (in_array(strtolower($field['kind']), $this->decimalNames)) {
            $result .= ' DOUBLE';
        } else {
            $result .= ' VARCHAR(255)';
        }

        if ($autoIncrement) {
            //
        } else if ((!array_key_exists('null', $field)) || (!$field['null'])) {
            $result .= ' NOT NULL';
        }

        if ($autoIncrement) {
            $result .= ' DEFAULT NULL';
        } else if (array_key_exists('default', $field)) {
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

        if ($autoIncrement) {
            $result .= ' PRIMARY KEY AUTO_INCREMENT';
        } else if (array_key_exists('index', $field)) {
            if ($field['index'] === 'primary') {
                $result .= ' PRIMARY KEY';
            } else if ($field['index'] === 'unique') {
                $result .= ' UNIQUE';
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
            $fields .= ($fields === '' ? '' : ', ') . '`' . $this->escape($fieldIndex) . '`';
            $fields .= $this->fieldToSQL($fieldItem);
        }

        if ($fields === '') {
            return false;
        } else {
            $sql = 'CREATE TABLE ' . '`' . $tableName . '`' . ' (' .
                $fields .
                ')';
        }

        return $this->query($sql) === false ? $this->connection->error : true;
    }

    // Add a column to table
    public function tableColumnAdd($tableName, $fieldName, $field)
    {
        $sql = 'ALTER TABLE ' . '`' . $tableName . '`' . ' ' .
            'ADD COLUMN ' . '`' . $fieldName . '`' . ' ' . $this->fieldToSQL($field);
        return $this->query($sql) === false ? $this->connection->error : true;
    }

    // Remove a column from table
    public function tableColumnDrop($tableName, $fieldName)
    {
        $sql = 'ALTER TABLE ' . '`' . $tableName . '`' . ' ' .
            'DROP COLUMN ' . '`' . $fieldName . '`';
        return $this->query($sql) === false ? $this->connection->error : true;
    }

    // Fetch tables information
    public function tableInfo($tableName)
    {
        $fieldsArray = array();

        $sql =
            'SELECT * ' .
            'FROM   information_schema.columns ' .
            'WHERE  table_schema = \'' . $this->escape($this->base) . '\' ' .
            'AND    table_name = \'' . $this->escape($tableName) . '\'';
        $query = $this->query($sql);
        if ($query !== false) {
            while ($row = $query->fetch()) {

                // MySQL type to beyond type
                if ($row['DATA_TYPE'] === 'longtext') {
                    $type = $this->textNames[0];
                } else if ($row['DATA_TYPE'] === 'varchar') {
                    $type = $this->stringNames[0];
                } else if ($row['DATA_TYPE'] === 'int') {
                    $type = $this->integerNames[0];
                } else if ($row['DATA_TYPE'] === 'double') {
                    $type = $this->decimalNames[0];
                } else {
                    $type = 'unknown:' . $row['DATA_TYPE'];
                }

                // Add to field list
                $fieldsArray[$row['COLUMN_NAME']] = array(
                    'kind' => $type,
                    'index' => (
                    $row['EXTRA'] === 'auto_increment' ? 'auto' :
                        ($row['COLUMN_KEY'] === 'PRI' ? 'primary' :
                            ($row['COLUMN_KEY'] === 'UNI' ? 'unique' : '')
                        )
                    ),
                    'null' => ($row['EXTRA'] === 'auto_increment' ? true : $row['IS_NULLABLE'] === 'YES')
                );

                // Default value
                $default = null;
                if ($row['EXTRA'] === 'auto_increment') {
                    $fieldsArray[$row['COLUMN_NAME']]['default'] = 'NULL';
                } else if (preg_match('/^[\'|"](.*)[\'|"]$/', $row['COLUMN_DEFAULT'], $matches)) {
                    $default = $matches[1];
                } else {
                    $default = $row['COLUMN_DEFAULT'];
                }
                if ($default !== null) {
                    $fieldsArray[$row['COLUMN_NAME']]['default'] = $default;
                }

            }
        }

        return $fieldsArray;
    }

    // Drop table
    public function tableDrop($tableName)
    {
        $sql = 'DROP TABLE ' . '`' . $tableName . '`';
        return $this->query($sql) === false ? $this->connection->error : true;
    }

    // Fetch list of tables
    public function tableList()
    {
        $result = array();
        $sql = 'SELECT table_name FROM information_schema.tables WHERE table_schema=\'' . $this->escape($this->base) . '\' ORDER BY table_name';
        $query = $this->query($sql);
        if ($query !== false) {
            while ($row = $query->fetch()) {
                array_push($result, $row['table_name']);
            }
        }
        return $result;
    }

}
