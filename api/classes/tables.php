<?php

include_once __DIR__ . '/../apiBaseClass.php';

class tables extends beyondApiBaseClass
{

    private $internalTables = array(
        "tableVersionInfo",
        "users"
    );

    /**
     * Fetch list of tables
     * @param string $data Parameters
     * @return array with result
     */
    public function fetch($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // No user input to check

        //
        $result = array();

        // Fetch list of tables on all databases
        foreach ($this->db->databases as $databaseName => $database) {
            $result[$databaseName] = $database->tableList();
        }

        // List of intrernal tables
        $internalTables = array();
        $internalPrefix = $this->config->get('base', 'site.prefix');
        foreach ($this->internalTables as $index => $name) {
            array_push($internalTables, $internalPrefix . $name);
        }

        return array(
            'fetch' => $result,
            'defaultDatabase' => $this->config->get('database', 'defaultDatabase'),
            'internalTables' => $internalTables
        );
    }

    /**
     * Get table configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function info($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'table', true, false);

        //
        $result = $this->db->databases[$data->database]->tableInfo($data->table);

        return array(
            'info' => $result
        );
    }

    /**
     * Drop table
     * @param string $data Parameters
     * @return array with result
     */
    public function drop($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);

        //
        if ($data->database === $this->config->get('database', 'defaultDatabase')) {
            $internalTables = array();
            $internalPrefix = $this->config->get('base', 'site.prefix');
            foreach ($this->internalTables as $index => $name) {
                array_push($internalTables, $internalPrefix . $name);
            }
            if (in_array($data->table, $internalTables)) {
                throw new Exception('Can not drop internal table [' . $data->table . '] from database [' . $data->database . ']');
            }
        }

        $result = $this->db->databases[$data->database]->tableDrop($data->table);
        return array(
            'drop' => $result
        );
    }

    /**
     * Create table
     * @param string $data Parameters
     * @return array with result
     */
    public function create($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);
        $this->checkObject($data, 'fields');

        //
        $result = $this->db->databases[$data->database]->tableCreate($data->table, (array)$data->fields);
        return array(
            'create' => $result
        );
    }

    /**
     * Add column to table
     * @param string $data Parameters
     * @return array with result
     */
    public function columnAdd($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);
        $this->checkString($data, 'field', true, false);
        $this->checkString($data, 'kind', true, false);
        $this->checkString($data, 'default', true, true);

        //
        if ($data->database === $this->config->get('database', 'defaultDatabase')) {
            $internalTables = array();
            $internalPrefix = $this->config->get('base', 'site.prefix');
            foreach ($this->internalTables as $index => $name) {
                array_push($internalTables, $internalPrefix . $name);
            }
            if (in_array($data->table, $internalTables)) {
                throw new Exception('Can not modify internal table [' . $data->table . '] from database [' . $data->database . ']');
            }
        }

        $result = $this->db->databases[$data->database]->tableColumnAdd($data->table, $data->field, array(
            'kind' => $data->kind,
            'index' => $data->index,
            'null' => $data->null,
            'default' => $data->default
        ));
        return array(
            'columnAdd' => $result
        );
    }

    /**
     * Remove column from table
     * @param string $data Parameters
     * @return array with result
     */
    public function columnDrop($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);
        $this->checkString($data, 'field', true, false);

        //
        if ($data->database === $this->config->get('database', 'defaultDatabase')) {
            $internalTables = array();
            $internalPrefix = $this->config->get('base', 'site.prefix');
            foreach ($this->internalTables as $index => $name) {
                array_push($internalTables, $internalPrefix . $name);
            }
            if (in_array($data->table, $internalTables)) {
                throw new Exception('Can not modify internal table [' . $data->table . '] from database [' . $data->database . ']');
            }
        }

        $result = $this->db->databases[$data->database]->tableColumnDrop($data->table, $data->field);
        return array(
            'columnDrop' => $result
        );
    }

    /**
     * Count rows of table
     * @param string $data Parameters
     * @return array with result
     */
    public function rowCount($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);

        //
        $result = $this->db->databases[$data->database]->count($data->table, array());
        return array(
            'rowCount' => $result
        );
    }

    /**
     * Query table
     * @param string $data Parameters
     * @return array with result
     */
    public function loadData($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);

        if (property_exists($data, 'offset')) {
            $this->checkInteger($data, 'offset', false, false, true);
        } else {
            $data->offset = false;
        }

        if (property_exists($data, 'limit')) {
            $this->checkInteger($data, 'limit', false, false, true);
        } else {
            $data->limit = false;
        }

        if (property_exists($data, 'order')) {
            $this->checkString($data, 'order', true, false);
        } else {
            $data->order = false;
        }

        //
        $result = array();

        $query = $this->db->databases[$data->database]->select($data->table, array('*'), array(), $data->offset, $data->limit, $data->order);
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->table . '] from database [' . $data->database . ']');
        }
        while ($row = $query->fetch()) {
            array_push($result, $row);
        }

        return array(
            'loadData' => $result
        );
    }

    /**
     * Add data to table
     * @param string $data Parameters
     * @return array with result
     */
    public function addData($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);
        $this->checkObject($data, 'fields');

        //
        $result = $this->db->databases[$data->database]->insert($data->table, (array)$data->fields);
        return array(
            'addData' => $result
        );
    }

    /**
     * Modify table data
     * @param string $data Parameters
     * @return array with result
     */
    public function modifyData($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'primary', true, false);
        $this->checkString($data, 'kind', true, false);
        $this->checkString($data, 'value', true, false);
        $this->checkObject($data, 'fields');

        //
        if (in_array($data->kind, array('number', 'decimal'))) {
            $where = array(
                $data->primary . ' = ' . $this->db->databases[$data->database]->escape($data->value)
            );
        } else {
            $where = array(
                $data->primary . ' = \'' . $this->db->databases[$data->database]->escape($data->value) . '\''
            );
        }

        $result = $this->db->databases[$data->database]->update(
            $data->table,
            (array)$data->fields,
            $where
        );

        return array(
            'modifyData' => $result
        );
    }

    /**
     * Remove data from tabl
     * @param string $data Parameters
     * @return array with result
     */
    public function deleteData($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);
        $this->checkString($data, 'table', true, false);
        $this->checkString($data, 'primary', true, false);
        $this->checkString($data, 'value', true, false);

        //
        $result = $this->db->databases[$data->database]->delete(
            $data->table,
            array(
                $data->primary . ' = \'' . $this->db->databases[$data->database]->escape($data->value) . '\''
            )
        );

        return array(
            'deleteData' => $result
        );
    }

}