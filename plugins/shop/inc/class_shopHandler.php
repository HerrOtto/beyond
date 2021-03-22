<?php

class shopHandler
{

    private $language; // string
    private $prefix; // string
    private $database; // beyondDatabaseDriver
    private $tools; // beyondTools
    private $config; // beyondConfig

    /**
     * Constructor
     * @param string $language Current user language
     * @param string $prefix Prefix
     * @param beyondDatabaseDriver $database Database connection of content plugin
     * @param beyondTools $tools Global beyond tools
     * @param beyondConfig $config Configuration
     */
    public function __construct($language, $prefix, $database, $tools, $config)
    {
        $this->language = $language;
        $this->prefix = $prefix;
        $this->database = $database;
        $this->tools = $tools;
        $this->config = $config;

        $this->cache = array();
    }

    /**
     * Output block
     * @param mixed $name Block name
     * @param mixed $language If "false" use current user language otherwise the specified
     */
    public function output($name, $language = false)
    {
        print PHP_EOL;

        try {

            // Load default values
            $configJson = file_get_contents(__DIR__ . '/../../../config/shop_settings.json');
            if (trim($configJson) === '') {
                $configObj = new stdClass();
            } else {
                $configObj = json_decode($configJson);
            }

            // Getting file configuration from database
            $query = $this->database->select(
                $this->prefix . 'shop_data',
                array(
                    '*'
                ),
                array(
                    'name = \'' . $this->database->escape($name) . '\''
                )
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'shop_data]');
            }
            if ($row = $query->fetch()) {
                $block = json_decode($row['content']);
            } else {
                return;
            }

            // Language
            if ($language === false) {
                $language = $this->language;
            }

            if (property_exists($block, $language)) {
                print $block->{$language};
            } else if (property_exists($block, 'default')) {
                print $block->{'default'};
            }

        } catch (Exception $e) {
            print "<!-- shop plugin exception: " . $e->getMessage() . ' -->' . PHP_EOL;
        }

        print PHP_EOL;
    }


}
