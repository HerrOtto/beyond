<?php

class shopDatabase
{

    private $prefix; // string

    /**
     * Constructor
     * @param string $prefix Prefix for this instance of beyond
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /*
     * Initialize plugin database
     * @param dbBaseClass $database Pointer to desired database object
     */
    public function init($database)
    {
        // Check the table tableVersionInfo holding the table versions
        if (!$database->tableExists($this->prefix . 'shop_tableVersionInfo')) {
            $database->tableCreate(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => array(
                        'kind' => 'string',
                        'index' => 'primary'
                    ),
                    'tableVersion' => array(
                        'kind' => 'number'
                    )
                )
            );
        }
        // Store table versions to key/value array
        $query = $database->select($this->prefix . 'shop_tableVersionInfo', array('tableName', 'tableVersion'), array());
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'shop_tableVersionInfo]');
        }
        $tableVersions = array();
        while ($row = $query->fetch()) {
            $tableVersions[$row['tableName']] = intval($row['tableVersion']);
        }

        // Init "shop_items" table
        if (!array_key_exists($this->prefix . 'shop_items', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'shop_items',
                array(
                    'id' => array(
                        'kind' => 'number',
                        'index' => 'auto'
                    ),
                    'articleNo' => array(
                        'kind' => 'string',
                        'index' => 'unique'
                    ),
                    'name' => array(
                        'kind' => 'string'
                    ),
                    'description' => array(
                        'kind' => 'longtext'
                    ),
                    'price' => array(
                        'kind' => 'decimal'
                    ),
                    'weightGramm' => array(
                        'kind' => 'number'
                    ),
                    'vatPercent' => array(
                        'kind' => 'decimal'
                    ),
                    'disabled' => array(
                        'kind' => 'number'
                    )
                )
            );
            $database->insert(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'shop_items',
                    'tableVersion' => 1
                )
            );
        }

        // Init "shop_orders" table
        if (!array_key_exists($this->prefix . 'shop_orders', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'shop_orders',
                array(
                    'id' => array(
                        'kind' => 'number',
                        'index' => 'auto'
                    ),
                    'shippingCompany' => array(
                        'kind' => 'string',
                        'index' => 'unique'
                    ),
                    'shippingName' => array(
                        'kind' => 'string'
                    ),
                    'shippingSurname' => array(
                        'kind' => 'string'
                    ),
                    'shippingStreetName' => array(
                        'kind' => 'string'
                    ),
                    'shippingStreetNo' => array(
                        'kind' => 'string'
                    ),
                    'shippingAddressAddition' => array(
                        'kind' => 'string'
                    ),
                    'shippingZip' => array(
                        'kind' => 'string'
                    ),
                    'shippingCity' => array(
                        'kind' => 'string'
                    ),
                    'shippingCountryCode' => array(
                        'kind' => 'string'
                    ),
                    'shippingVat' => array(
                        'kind' => 'string'
                    ),
                    'shippingMail' => array(
                        'kind' => 'string'
                    ),
                    'invoiceCompany' => array(
                        'kind' => 'string',
                        'index' => 'unique'
                    ),
                    'invoiceName' => array(
                        'kind' => 'string'
                    ),
                    'invoiceSurname' => array(
                        'kind' => 'string'
                    ),
                    'invoiceStreetName' => array(
                        'kind' => 'string'
                    ),
                    'invoiceStreetNo' => array(
                        'kind' => 'string'
                    ),
                    'invoiceAddressAddition' => array(
                        'kind' => 'string'
                    ),
                    'invoiceZip' => array(
                        'kind' => 'string'
                    ),
                    'invoiceCity' => array(
                        'kind' => 'string'
                    ),
                    'invoiceCountryCode' => array(
                        'kind' => 'string'
                    ),
                    'invoiceVat' => array(
                        'kind' => 'string'
                    ),
                    'invoiceMail' => array(
                        'kind' => 'string'
                    ),
                    'payment' => array(
                        'kind' => 'string'
                    ),
                    'paymentDataJson' => array(
                        'kind' => 'string'
                    ),
                    'amountInvoiceInclTax' => array(
                        'kind' => 'decimal'
                    ),
                    'amountPayed' => array(
                        'kind' => 'decimal'
                    ),
                )
            );
            $database->insert(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'shop_orders',
                    'tableVersion' => 1
                )
            );
        }

        // Init "shop_order_items" table
        if (!array_key_exists($this->prefix . 'shop_order_items', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'shop_order_items',
                array(
                    'id' => array(
                        'kind' => 'number',
                        'index' => 'auto'
                    ),
                    'orderId' => array(
                        'kind' => 'number'
                    ),
                    'articleNo' => array(
                        'kind' => 'string',
                        'index' => 'unique'
                    ),
                    'name' => array(
                        'kind' => 'string'
                    ),
                    'description' => array(
                        'kind' => 'longtext'
                    ),
                    'unit' => array(
                        'kind' => 'number'
                    ),
                    'unitText' => array(
                        'kind' => 'string'
                    ),
                    'package' => array(
                        'kind' => 'number'
                    ),
                    'pricePerPackage' => array(
                        'kind' => 'decimal'
                    ),
                    'weightPerPackage' => array(
                        'kind' => 'string'
                    ),
                    'vatPercent' => array(
                        'kind' => 'decimal'
                    ),
                    'packagesOrdered' => array(
                        'kind' => 'number'
                    )
                )
            );
            $database->insert(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'shop_order_items',
                    'tableVersion' => 1
                )
            );
        }

        // Init "shop_order_items" table
        if (!array_key_exists($this->prefix . 'shop_coupons', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'shop_coupons',
                array(
                    'id' => array(
                        'kind' => 'number',
                        'index' => 'auto'
                    ),
                    'code' => array(
                        'kind' => 'string',
                        'index' => 'unique'
                    ),
                    'value' => array(
                        'kind' => 'decimal'
                    ),
                    'kind' => array(
                        'kind' => 'string'
                    ),
                    'disabled' => array(
                        'kind' => 'number'
                    )
                )
            );
            $database->insert(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'shop_coupons',
                    'tableVersion' => 1
                )
            );
        }

        // Init "shop_countries" table
        if (!array_key_exists($this->prefix . 'shop_countries', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'shop_countries',
                array(
                    'id' => array(
                        'kind' => 'number',
                        'index' => 'auto'
                    ),
                    'code' => array(
                        'kind' => 'string',
                        'index' => 'unique'
                    ),
                    'value' => array(
                        'kind' => 'string'
                    )
                )
            );
            $database->insert(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'shop_countries',
                    'tableVersion' => 1
                )
            );
        }

        // Init "shop_shipping" table
        if (!array_key_exists($this->prefix . 'shop_shipping', $tableVersions)) {
            $database->tableCreate(
                $this->prefix . 'shop_shipping',
                array(
                    'id' => array(
                        'kind' => 'number',
                        'index' => 'auto'
                    ),
                    'countryCode' => array(
                        'kind' => 'string'
                    ),
                    'weight' => array(
                        'kind' => 'number'
                    ),
                    'value' => array(
                        'kind' => 'decimal'
                    )
                )
            );
            $database->insert(
                $this->prefix . 'shop_tableVersionInfo',
                array(
                    'tableName' => $this->prefix . 'shop_shipping',
                    'tableVersion' => 1
                )
            );
        }

    }

    /*
     * Drop plugin database
     * @param dbBaseClass $database Pointer to desired database object
     */
    public function drop($database)
    {
        $database->tableDrop($this->prefix . 'shop_tableVersionInfo');
        $database->tableDrop($this->prefix . 'shop_items');
        $database->tableDrop($this->prefix . 'shop_orders');
        $database->tableDrop($this->prefix . 'shop_order_items');
        $database->tableDrop($this->prefix . 'shop_coupons');
    }

}