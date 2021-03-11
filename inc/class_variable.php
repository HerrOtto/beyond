<?php

/**
 * Get HTTP GET or POST variables
 * @author     Tim David Saxen <info@netzmal.de>
 */
class variable
{

    /**
     * Get configuration value
     * @param string $variableName Variable name
     * @param string $replacement Default value if variable not exists
     * @return string
     */
    public function get($variableName, $replacement = null)
    {
        if (array_key_exists($variableName, $_POST)) {
            return $_POST[$variableName];
        } else if (array_key_exists($variableName, $_GET)) {
            return $_GET[$variableName];
        } else if (is_null($replacement)) {
            throw new Exception('Missing HTTP variable [' . $variableName . '] and no replacement defined');
        } else {
            return $replacement;
        }
    }

}