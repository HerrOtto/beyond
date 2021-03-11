<?php

require_once __DIR__ . '/../../../api/apiBaseClass.php';

class content_config extends apiBaseClass
{

    /**
     * Load configuration
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
        $configJson = file_get_contents(__DIR__ . '/../../../config/content_settings.json');
        $config = json_decode($configJson, JSON_OBJECT_AS_ARRAY);

        if (!property_exists($config, 'version')) {
            $config->version = 1;
        }

        if (!property_exists($config, 'database')) {
            $config->database = $this->config->get('database', 'defaultDatabase');
        }

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
        // Check permissions
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);

        //
        $configJson = file_get_contents(__DIR__ . '/../../../config/content_settings.json');
        $config = json_decode($configJson);

        if (!property_exists($config, 'version')) {
            $config->version = 1;
        }

        $config->database = $data->database;

        file_put_contents(__DIR__ . '/../../../config/content_settings.json', json_encode($config, JSON_PRETTY_PRINT));

        return array(
            'save' => true
        );
    }

}