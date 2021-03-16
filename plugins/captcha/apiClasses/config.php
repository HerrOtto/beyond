<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';

class captcha_config extends beyondApiBaseClass
{

    /**
     * Load configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function load($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // No user input

        //
        $configJson = file_get_contents(__DIR__ . '/../../../config/captcha_settings.json');
        $config = json_decode($configJson, JSON_OBJECT_AS_ARRAY);

        return array(
            'load' => $config
        );
    }

    /**
     * Save configuration
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
        $this->checkObject($data, 'apperence');
        $this->checkObject($data, 'security');

        //
        $configJson = file_get_contents(__DIR__ . '/../../../config/captcha_settings.json');
        $config = json_decode($configJson);

        if (!property_exists($config, 'version')) {
            $config->version = 1;
        }

        if (!property_exists($config, 'apperence')) {
            $config->apperence = new stdClass();
            $config->apperence->width = 200;
            $config->apperence->height = 70;
        }
        $config->apperence->width = $data->apperence->width;
        $config->apperence->height = $data->apperence->height;

        if (!property_exists($config, 'security')) {
            $config->security = new stdClass();
            $config->security->minLength = 5;
            $config->security->maxLength = 8;
            $config->security->maxRotation = 8;
        }

        $config->security->minLength = $data->security->minLength;
        $config->security->maxLength = $data->security->maxLength;
        $config->security->maxRotation = $data->security->maxRotation;

        file_put_contents(__DIR__ . '/../../../config/captcha_settings.json', json_encode($config, JSON_PRETTY_PRINT));

        return array(
            'save' => true
        );
    }

}