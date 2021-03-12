<?php

/**
 * Configuration class
 * @author     Tim David Saxen <info@netzmal.de>
 */
class beyondConfig
{

    private $data = array();

    /**
     * Constructor
     */
    function __construct()
    {
        foreach (glob(__DIR__ . "/../../config/*.json") as $fileName) {
            $fileJson = file_get_contents($fileName);
            $this->data[basename($fileName, '.json')] = json_decode($fileJson, true);
        }

    }

    /**
     * Get configuration value
     * @param string $configFile JSON Filename without extension
     * @param string $jsonPath Path within the JSON file (dot seperated)
     * @param mixed $jsonPath If != null and path is not resolvable then return this value otherwise (default) throw exception
     * @return mixed
     */
    public function get($configFile, $jsonPath, $replacement = null)
    {
        if (!array_key_exists($configFile, $this->data)) {
            throw new Exception('Configuration file not found [' . $configFile . '.json]');
        }

        // Point to config file
        $current = $this->data[$configFile];

        // Walk through $jsonPath
        $jsonPathArray = explode('.', $jsonPath);
        foreach ($jsonPathArray as $pathIndex => $pathItem) {
            if (!array_key_exists($pathItem, $current)) {
                if ($replacement !== null) {
                    return $replacement;
                } else {
                    throw new Exception('Configuration path not resolvable [' . $jsonPath . '] missing part [' . $pathItem . '] within configuration file [' . $configFile . '.json]');
                }
            }
            $current = $current[$pathItem];
        }

        return $current;
    }

}