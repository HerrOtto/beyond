<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_shopTools.php';

class shop_config extends beyondApiBaseClass
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

        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();

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
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();
        if ($configObj->database !== $data->database) {

            // Copy tables to new Database
            $databaseCurrent = $this->db->databases[$configObj->database];
            $databaseNew = $this->db->databases[$data->database];

            $tables = array(
                $this->prefix . 'shop_coupons',
                $this->prefix . 'shop_items',
                $this->prefix . 'shop_order_items',
                $this->prefix . 'shop_orders',
                $this->prefix . 'shop_tableVersionInfo'
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

                // Change database in configuration file
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

        file_put_contents(__DIR__ . '/../../../config/shop_settings.json', json_encode($configObj, JSON_PRETTY_PRINT));

        return array(
            'save' => true
        );
    }

}