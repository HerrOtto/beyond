<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_shopTools.php';

class shop_coupons extends beyondApiBaseClass
{

    /**
     * Load coupons from database
     * @param string $data Parameters
     * @return array with result
     */
    public function fetch($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input - No input

        // Load config
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();

        // Current Database
        $databaseCurrent = $this->db->databases[$configObj->database];

        // Init result
        $shop = new stdClass();

        // Load data from current database
        $query = $databaseCurrent->select(
            $this->prefix . 'shop_coupons',
            array(
                '*'
            ),
            array()
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'shop_coupons]');
        }
        while ($row = $query->fetch()) {
            $id = $row['id'];
            unset($row['id']);
            $row['disabled'] = intval($row['disabled']) === 1;
            $shop->{$id} = $row;
        }

        return array(
            'fetch' => $shop
        );
    }

    /**
     * Add coupon
     * @param string $data Parameters
     * @return array with result
     */

    public function add($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'code', true, false);
        $this->checkFloat($data, 'value', false, false, false);
        $this->checkString($data, 'kind', true, false);
        $this->checkBoolean($data, 'disabled');

        // Load configuration
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();
        $database = $this->db->databases[$configObj->database];

        // Load data from current database
        $query = $database->insert(
            $this->prefix . 'shop_coupons',
            array(
                'code' => $data->code,
                'value' => $data->value,
                'kind' => $data->kind,
                'disabled' => ($data->disabled === true ? 1 : 0)
            )
        );

        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'shop_coupons]');
        }

        return array(
            'add' => true
        );
    }

    /**
     * Save coupon
     * @param string $data Parameters
     * @return array with result
     */

    public function save($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkInteger($data, 'id', true, false, false);
        $this->checkString($data, 'code', true, false);
        $this->checkFloat($data, 'value', false, false, false);
        $this->checkString($data, 'kind', true, false);
        $this->checkBoolean($data, 'disabled');

        // Load configuration
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();
        $database = $this->db->databases[$configObj->database];

        // Load data from current database
        $query = $database->update(
            $this->prefix . 'shop_coupons',
            array(
                'code' => $data->code,
                'value' => $data->value,
                'kind' => $data->kind,
                'disabled' => ($data->disabled === true ? 1 : 0)
            ),
            array(
                'id = \'' . $database->escape($data->id) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'shop_coupons]');
        }

        return array(
            'save' => true
        );
    }

    /**
     * Delete coupon
     * @param string $data Parameters
     * @return array with result
     */

    public function delete($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkInteger($data, 'id', false, false, false);

        // Load configuration
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();
        $database = $this->db->databases[$configObj->database];

        // Load data from current database
        $query = $database->delete(
            $this->prefix . 'shop_coupons',
            array(
                'id = \'' . $database->escape($data->id) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not delete from table [' . $this->prefix . 'shop_coupons]');
        }

        return array(
            'delete' => true
        );
    }

}