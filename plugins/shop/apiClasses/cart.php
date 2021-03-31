<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_shopTools.php';

class shop_cart extends beyondApiBaseClass
{

    /**
     * Fetch cart
     * @param string $data Parameters
     * @return array with result
     */

    public function fetch($data)
    {

        // Check permissions - No permissions required

        // Check user input - No input

        return array(
            'fetch' => $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart']
        );

    }

    /**
     * Add article to cart
     * @param string $data Parameters
     * @return array with result
     */

    public function addItem($data)
    {

        // Check permissions - No permissions required
        // Check user input
        $this->checkString($data, 'articleNo', true, false);
        $this->checkInteger($data, 'amount', 1, false);

        // Load config
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();

        // Current Database
        $databaseCurrent = $this->db->databases[$configObj->database];

        // WHERE limit
        $where = array();
        $where = array_merge($where, array('disabled = 0'));
        $where = array_merge($where, array('articleNo = \'' . $databaseCurrent->escape($data->articleNo) . '\''));

        // Load data from current database
        $query = $databaseCurrent->select(
            $this->prefix . 'shop_items',
            array(
                '*'
            ),
            $where
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'shop_items]');
        }

        $item = false;
        if ($row = $query->fetch()) {
            unset($row['disabled']);
            $row['name'] = json_decode($row['name']);
            $row['description'] = json_decode($row['description']);
            $row['disabled'] = intval($row['disabled']) === 1;
            $item = (object)$row;
        }

        if ($item === false) {
            throw new Exception('Article [' . $data->articleNo . '] not found');
        } else if (!array_key_exists($data->articleNo, $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'])) {
            $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'][$data->articleNo] = $item;
            $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount = $data->amount;
        } else {
            if (property_exists($_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'][$data->articleNo], 'amount')) {
                $amount = $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount;
            } else {
                $amount = 1;
            }
            $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'][$data->articleNo] = $item;
            $_SESSION[$beyond->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount = $amount + $data->amount;
        }

        return array(
            'addItem' => true
        );

    }

    private function calculate()
    {

        //

    }

    public function getCartHtml($data)
    {

        //

    }

    public function getAddressMask($data)
    {

        //

    }

    public function getCheckoutMask($data)
    {

        //

    }

    public function getPayMask($data)
    {

        //

    }

    public function getPayNowMask($data)
    {

        //

    }

    public function getSentMask($data)
    {

        //

    }

    public function updateItem($data)
    {

        //

    }

    public function removeItem($data)
    {

        //

    }

    public function checkout($data)
    {

        //

    }

    public function pay($data)
    {

        //

    }

    public function send($data)
    {

        //

    }

}