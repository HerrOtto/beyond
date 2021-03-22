<?php

class shopTools
{

    function getConfig() {

        $configJson = file_get_contents(__DIR__ . '/../../../config/shop_settings.json');


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

        return $configObj;
    }

}
