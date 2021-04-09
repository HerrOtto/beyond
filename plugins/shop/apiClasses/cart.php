<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_shopTools.php';

class shop_cart extends beyondApiBaseClass
{

    /**
     * Fetch cart items
     * @param string $data Parameters
     * @return array with result
     */

    public function fetch($data)
    {

        // Check permissions - No permissions required

        // Check user input - No input

        return array(
            'fetch' => $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'],
            'coupon' => $_SESSION[$this->prefix . 'data']['plugin_shop']['coupon'] // Default: false
        );

    }

    /**
     * Add item to cart
     * @param string $data Parameters
     * @return array with result
     */

    public function addItem($data)
    {

        // Check permissions - No permissions required

        // Check user input
        $this->checkString($data, 'articleNo', true, false);
        $this->checkInteger($data, 'amount', 1, false);

        // Fetch item from database
        $item = $this->getItem($data->articleNo);

        // Add item to cart
        if ($item === false) {
            // Unknown item
            throw new Exception('Article [' . $data->articleNo . '] not found');
        } else if (!array_key_exists($data->articleNo, $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'])) {
            // New item
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo] = $item;
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount = $data->amount;
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->priceOriginal = $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->price;
        } else {
            // Existing item
            if (property_exists($_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo], 'amount')) {
                $amount = $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount;
            } else {
                $amount = 1;
            }
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo] = $item;
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount = $amount + $data->amount;
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->priceOriginal = $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->price;
        }

        // Calculate cart
        $this->calculate();

        return array(
            'addItem' => true
        );

    }

    /**
     * Remove item from cart
     * @param string $data Parameters
     * @return array with result
     */

    public function removeItem($data)
    {

        // Check permissions - No permissions required

        // Check user input
        $this->checkString($data, 'articleNo', true, false);

        // Remove item
        if (array_key_exists($data->articleNo, $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'])) {
            unset($_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]);
        }

        // Calculate cart
        $this->calculate();

        return array(
            'removeItem' => true
        );

    }

    /**
     * Modify cart item
     * @param string $data Parameters
     * @return array with result
     */

    public function modifyItem($data)
    {

        // Check permissions - No permissions required

        // Check user input
        $this->checkString($data, 'articleNo', true, false);
        $this->checkInteger($data, 'amount', 1, false);

        // Fetch item from database
        $item = $this->getItem($data->articleNo);

        // Modify item (Set to "amount")
        if ($item === false) {
            if (array_key_exists($data->articleNo, $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'])) {
                unset($_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]);
            }
        } else if (array_key_exists($data->articleNo, $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'])) {
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo] = $item;
            $_SESSION[$this->prefix . 'data']['plugin_shop']['cart'][$data->articleNo]->amount = $data->amount;
        }

        // Calculate cart
        $this->calculate();

        return array(
            'removeItem' => true
        );

    }

    /**
     * Activate discount coupon
     * @param string $data Parameters
     * @return array with result
     */

    public function activateCoupon($data)
    {

        // Check permissions - No permissions required

        // Check user input
        $this->checkString($data, 'couponCode', true, false);

        // Fetch item from database
        $item = $this->getCoupon($data->couponCode);

        // Modify item (Set to "amount")
        if ($item === false) {
            throw new Exception('Coupon code [' . $data->couponCode . '] not found');
        } else {
            $_SESSION[$this->prefix . 'data']['plugin_shop']['coupon'] = $item;
        }

        // Calculate cart
        $this->calculate();

        return array(
            'activateCoupon' => true
        );

    }

    /**
     * Calculate cart, has to be called on all cart updates
     */

    private function calculate()
    {

        // Get coupon code details
        $couponEuro = 0;
        $couponPercent = 0;
        if ($_SESSION[$this->prefix . 'data']['plugin_shop']['coupon'] !== false) {
            if ($_SESSION[$this->prefix . 'data']['plugin_shop']['coupon']->kind === 'percent') {
                $couponPercent = $_SESSION[$this->prefix . 'data']['plugin_shop']['coupon']->value;
            } else if ($_SESSION[$this->prefix . 'data']['plugin_shop']['coupon']->kind === 'amount') {
                $couponEuro = $_SESSION[$this->prefix . 'data']['plugin_shop']['coupon']->value;
            }
        }

        // Calculate price
        foreach ($_SESSION[$this->prefix . 'data']['plugin_shop']['cart'] as $itemArticleNo => $item) {

            // Calculate discount
            if ($couponEuro > 0) {
                if ($couponEuro > $item->priceOriginal) {
                    $item->price = 0;
                    $couponEuro -= $item->priceOriginal;
                } else {
                    $item->price = $item->priceOriginal - $couponEuro;
                    $couponEuro = 0;
                }
            } else if ($couponPercent > 0) {
                $item->price = $item->priceOriginal - ($item->priceOriginal / 100 * $couponPercent);
            }

        }

    }

    /**
     * Get item from database
     * @param string $articleNo The item number to fetch from database
     * @return object with item details
     */

    private function getItem($articleNo)
    {

        // Load config
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();

        // Current Database
        $databaseCurrent = $this->db->databases[$configObj->database];

        // WHERE limit
        $where = array();
        $where = array_merge($where, array('disabled = 0'));
        $where = array_merge($where, array('articleNo = \'' . $databaseCurrent->escape($articleNo) . '\''));

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

        return $item;

    }

    /**
     * Get coupon details from database
     * @param string $couponCode The coupon code to fetch from database
     * @return object with coupon details
     */

    private function getCoupon($couponCode)
    {

        // Load config
        $shopTools = new shopTools();
        $configObj = $shopTools->getConfig();

        // Current Database
        $databaseCurrent = $this->db->databases[$configObj->database];

        // WHERE limit
        $where = array();
        $where = array_merge($where, array('disabled = 0'));
        $where = array_merge($where, array('code = \'' . $databaseCurrent->escape($couponCode) . '\''));

        // Load data from current database
        $query = $databaseCurrent->select(
            $this->prefix . 'shop_coupons',
            array(
                '*'
            ),
            $where
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'shop_coupons]');
        }

        $item = false;
        if ($row = $query->fetch()) {
            unset($row['disabled']);
            unset($row['id']);
            $row['code'] = $row['code'];
            $row['value'] = $row['value'];
            $row['kind'] = $row['kind'];
            $item = (object)$row;
        }

        return $item;

    }

}