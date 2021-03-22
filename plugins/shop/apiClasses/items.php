<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_shopTools.php';

class shop_items extends beyondApiBaseClass
{

    /**
     * Load items from database
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
            $this->prefix . 'shop_items',
            array(
                '*'
            ),
            array()
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'shop_items]');
        }
        while ($row = $query->fetch()) {
            $id = $row['id'];
            unset($row['id']);
            $row['name'] = json_decode($row['name']);
            $row['description'] = json_decode($row['description']);
            $row['disabled'] = intval($row['disabled']) === 1;
            $shop->{$id} = $row;
        }

        return array(
            'fetch' => $shop
        );
    }

    /**
     * Add item
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
        $this->checkString($data, 'articleNo', true, false);
        $this->checkFloat($data, 'price', false, false, false);
        $this->checkInteger($data, 'weightGramm', false, false, false);
        $this->checkFloat($data, 'vatPercent', false, false, false);
        $this->checkBoolean($data, 'disabled');

        foreach ($this->languages as $lang => $langName) {
            $this->checkString($data, 'name_' . $lang, true, false);
            $this->checkString($data, 'description_' . $lang, true, false);
        }

        // Load configuration
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();
        $database = $this->db->databases[$configObj->database];

        // Init content
        $name = new stdClass();
        $description = new stdClass();
        foreach ($this->languages as $lang => $langName) {
            $name->{$lang} = $data->{'name_' . $lang};
            $description->{$lang} = $data->{'name_' . $lang};
        }

        // Load data from current database
        $query = $database->insert(
            $this->prefix . 'shop_items',
            array(
                'articleNo' => $data->articleNo,
                'price' => $data->price,
                'weightGramm' => $data->weightGramm,
                'vatPercent' => $data->vatPercent,
                'disabled' => ($data->disabled === true ? 1 : 0),
                'name' => json_encode($name),
                'description' => json_encode($description)
            )
        );

        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'shop_items]');
        }

        return array(
            'add' => true
        );
    }

    /**
     * Save item
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
        $this->checkString($data, 'articleNo', true, false);
        $this->checkFloat($data, 'price', false, false, false);
        $this->checkInteger($data, 'weightGramm', false, false, false);
        $this->checkFloat($data, 'vatPercent', false, false, false);
        $this->checkBoolean($data, 'disabled');

        foreach ($this->languages as $lang => $langName) {
            $this->checkString($data, 'name_' . $lang, true, false);
            $this->checkString($data, 'description_' . $lang, true, false);
        }

        // Load configuration
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();
        $database = $this->db->databases[$configObj->database];

        // Init content
        $name = new stdClass();
        $description = new stdClass();
        foreach ($this->languages as $lang => $langName) {
            $name->{$lang} = $data->{'name_' . $lang};
            $description->{$lang} = $data->{'name_' . $lang};
        }

        // Load data from current database
        $query = $database->update(
            $this->prefix . 'shop_items',
            array(
                'articleNo' => $data->articleNo,
                'price' => $data->price,
                'weightGramm' => $data->weightGramm,
                'vatPercent' => $data->vatPercent,
                'disabled' => ($data->disabled === true ? 1 : 0),
                'name' => json_encode($name),
                'description' => json_encode($description)
            ),
            array(
                'id = \'' . $database->escape($data->id) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'shop_items]');
        }

        return array(
            'save' => true
        );
    }

    /**
     * Delete item
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
            $this->prefix . 'shop_items',
            array(
                'id = \'' . $database->escape($data->id) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not delete from table [' . $this->prefix . 'shop_items]');
        }

        return array(
            'delete' => true
        );
    }

}