<?php

class contentHandler
{

    private $language; // string
    private $prefix; // string
    private $database; // beyondDatabaseDriver
    private $tools; // beyondTools
    private $config; // beyondConfig

    private array $cache;

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
     * Get field data
     * @param string $fieldName Field name to get
     * @param bool $print If "true" print and return the result otherwise just return
     * @param mixed $language If "false" use current user language otherwise the specified
     * @result string Field content
     */
    public function get($fieldName, $print = true, $language = false)
    {
        $backfiles = debug_backtrace();
        $callerFileAndPath = $backfiles[0]['file'];
        $callerFile = basename($backfiles[0]['file']);
        $callerPath = pathinfo($callerFileAndPath, PATHINFO_DIRNAME);
        $baseDir = $this->config->get('base', 'server.absPath');

        // Get relative path to baseDir
        if (substr($callerPath, 0, strlen($baseDir)) !== $baseDir) {
            throw new Exception('Calling script [' . $callerFile . '] not in a subfolder of [' . $baseDir . ']');
        }
        $callerRelativePath = substr($callerPath, strlen($baseDir));

        // Check if the path is valid
        $dir = $this->tools->checkDirectory($callerRelativePath);
        if (!$dir['isValid']) {
            throw new Exception('Calling script [' . $callerFile . '] is not valid');
        }

        // Check cache
        if (array_key_exists($dir['relPath'] . '/' . $callerFile, $this->cache)) {

            // Use cached data
            $fielConfigObj = $this->cache[$dir['relPath'] . '/' . $callerFile];

        } else {

            // Getting file configuration from database
            $query = $this->database->select(
                $this->prefix . 'content_data',
                array(
                    '*'
                ),
                array(
                    'filePathName = \'' . $this->database->escape(ltrim($dir['relPath'] . '/' . $callerFile,'/')) . '\''
                )
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'content_data]');
            }

            $fileData = false;
            if ($row = $query->fetch()) {
                $fileData = $row['dataJson'];
            }
            if (($fileData === false) || (trim($fileData) === '')) {
                throw new Exception('Content not found');
            }

            $fielConfigObj = json_decode($fileData);
            if (!property_exists($fielConfigObj, 'fields')) {
                throw new Exception('Content not found');
            }

            $this->cache[$dir['relPath'] . '/' . $callerFile] = $fielConfigObj;

        }

        if (!property_exists($fielConfigObj->fields, $fieldName)) {
            throw new Exception('Content field [' . $fieldName . '] not found');
        }

        // Get value
        $value = "";
        if ($language === false) {
            $language = $this->language;
        }
        if (!property_exists($fielConfigObj->fields->{$fieldName}, $language)) {
            if (property_exists($fielConfigObj->fields->{$fieldName}, 'default')) {
                $value = $fielConfigObj->fields->{$fieldName}->{'default'};
            } else {
                throw new Exception('Content field [' . $fieldName . '] not found');
            }
        } else {
            $value = $fielConfigObj->fields->{$fieldName}->{$language};
        }

        // Output
        if ($print !== false) {
            print $value;
        }
        return $value;
    }


}
