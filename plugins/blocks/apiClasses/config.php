<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';

class blocks_config extends beyondApiBaseClass
{

    /**
     * Load base configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function load($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // No user input

        //
        $configJson = file_get_contents(__DIR__ . '/../../../config/blocks_settings.json');
        if (trim($configJson) === '') {
            $configObj = new stdClass();
        } else {
            $configObj = json_decode($configJson);
        }

        if (!property_exists($configObj, 'version')) {
            $configObj->version = 1;
        }

        if (!property_exists($configObj, 'database')) {
            $configObj->database = $this->config->get('database', 'defaultDatabase');
        }

        return array(
            'load' => $configObj
        );
    }

    /**
     * Save base configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function save($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);

        // Does this Database exists?
        if (!array_key_exists($data->database, $this->db->databases)) {
            throw new Exception('Unknown database [' . $data->database . '] selected');
        }

        // Database changed?
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        if ($configObj->database !== $data->database) {

            // Copy tables to new Database
            $databaseCurrent = $this->db->databases[$configObj->database];
            $databaseNew = $this->db->databases[$data->database];

            $tables = array(
                $this->prefix . 'blocks_data',
                $this->prefix . 'blocks_tableVersionInfo'
            );

            try {

                // Copy data to new database
                foreach ($tables as $tableIndex => $tableName) {
                    $this->db->moveTable(false, $databaseCurrent, $databaseNew, $tableName);
                }

                // On success drop old tables
                foreach ($tables as $tableIndex => $tableName) {
                    try {
                        $databaseCurrent->tableDrop($tableName);
                    } catch (Exception $e) {
                        // Ignore exception
                    }
                }

                // Change database in configuration
                $configObj->database = $data->database;

            } catch (Exception $e) {

                // On failure drop new tables
                foreach ($tables as $tableIndex => $tableName) {
                    try {
                        $databaseNew->tableDrop($tableName);
                    } catch (Exception $e) {
                        // Ignore exception
                    }
                }

            }
        }

        file_put_contents(__DIR__ . '/../../../config/blocks_settings.json', json_encode($configObj, JSON_PRETTY_PRINT));

        return array(
            'save' => true
        );
    }

    /**
     * Load blocks drom database
     * @param string $data Parameters
     * @return array with result
     */
    public function loadBlocks($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input - No input

        // Load config
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));

        // Current Database
        $databaseCurrent = $this->db->databases[$configObj->database];

        // Init result
        $blocks = new stdClass();

        // Load data from current database
        $query = $databaseCurrent->select(
            $this->prefix . 'blocks_data',
            array(
                'name',
                'kind',
                'content'
            ),
            array()
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'blocks_data]');
        }
        while ($row = $query->fetch()) {
            $blocks->{$row['name']} = array(
                'kind' => $row['kind'],
                'content' => json_decode($row['content'])
            );
        }

        return array(
            'loadBlocks' => $blocks
        );
    }

    /**
     * Add block
     * @param string $data Parameters
     * @return array with result
     */
    public function addBlock($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'name', true, false);
        $this->checkString($data, 'kind', true, false);
        foreach ($this->languages as $lang => $langName) {
            $this->checkString($data, 'value_' . $lang, true, false);
        }

        // Load configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Init content
        $content = new stdClass();
        foreach ($this->languages as $lang => $langName) {
            $content->{$lang} = $data->{'value_' . $lang};
        }

        // Load data from current database
        $query = $database->insert(
            $this->prefix . 'blocks_data',
            array(
                'name' => $data->name,
                'kind' => $data->kind,
                'content' => json_encode($content)
            )
        );
        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'blocks_data]');
        }

        return array(
            'addBlock' => true
        );
    }

    /**
     * Save block
     * @param string $data Parameters
     * @return array with result
     */
    public function saveBlock($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'name', true, false);
        foreach ($this->languages as $lang => $langName) {
            $this->checkString($data, 'value_' . $lang, true, false);
        }

        // Load configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Init content
        $content = new stdClass();
        foreach ($this->languages as $lang => $langName) {
            $content->{$lang} = $data->{'value_' . $lang};
        }

        // Load data from current database
        $query = $database->update(
            $this->prefix . 'blocks_data',
            array(
                'content' => json_encode($content)
            ),
            array(
                'name = \'' . $database->escape($data->name) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'blocks_data]');
        }

        return array(
            'saveBlock' => true
        );
    }

    /**
     * Delete block
     * @param string $data Parameters
     * @return array with result
     */
    public function deleteBlock($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'name', true, false);

        // Load configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Init content
        $content = new stdClass();
        foreach ($this->languages as $lang => $langName) {
            $content->{$lang} = $data->{'value_' . $lang};
        }

        // Load data from current database
        $query = $database->delete(
            $this->prefix . 'blocks_data',
            array(
                'name = \'' . $database->escape($data->name) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not delete from table [' . $this->prefix . 'blocks_data]');
        }

        return array(
            'deleteBlock' => true
        );
    }

}