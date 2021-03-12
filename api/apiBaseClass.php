<?php

/**
 * API base class
 * @author     Tim David Saxen <info@netzmal.de>
 */
abstract class beyondApiBaseClass
{
    protected beyondConfig $config;
    protected beyondVariable $variable;
    protected beyondDatabaseConnection $db;
    protected string $prefix;
    protected array $languages;
    protected beyondTools $tools;

    /**
     * Constructor
     * @param object $config Pointer to configuration object
     * @param string $variable Pointer to variable object
     * @param string $db Pointer to database object
     * @param string $prefix Prefix for this instance of beyond
     * @param array $languages Array of configured languages
     * @return mixed
     */
    function __construct(&$config, &$variable, &$db, $prefix, $languages, $tools)
    {
        $this->config = $config;
        $this->variable = $variable;
        $this->db = $db;
        $this->prefix = $prefix;
        $this->languages = $languages;
        $this->tools = $tools;
    }

    /**
     * Check if variable is a integer or a string containing an integer
     * @param object $object Variable
     * @param string $propertyName Variable
     * @param int $min Min. allowed value
     * @param int $max Max. alowed value
     * @param int $allowFalse Allow boolean value "false" as value
     */
    protected function checkInteger(&$object, $propertyName, $min = false, $max = false, $allowFalse = false)
    {
        if (!property_exists($object, $propertyName)) {
            throw new Exception('Property [' . $propertyName . '] not defined');
        }
        $value = $object->{$propertyName};

        if (($allowFalse === true) && ($value === false)) {
            return $value;
        }

        if (!is_numeric($value)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] is not a number');
        }

        if (floatval($value) != intval($value)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] is a decimal value not a number');
        }

        if (($min !== false) && (intval($value) > $min)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] exceeds expected min. value [' . $min . ']');
        }

        if (($max !== false) && (intval($value) > $max)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] exceeds expected min. value [' . $max . ']');
        }

        $object->{$propertyName} = intval($value);
    }

    /**
     * Check if variable is a string
     * @param object $object Variable
     * @param string $propertyName Variable
     * @param bool $trim Trim string
     * @param bool $allowEmpty Allow empty string
     */
    protected function checkString(&$object, $propertyName, $trim = true, $allowEmpty = true)
    {
        if (!property_exists($object, $propertyName)) {
            throw new Exception('Property [' . $propertyName . '] not defined');
        }
        $value = $object->{$propertyName};

        if (is_array($value)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] is an array and not a string');
        }

        if (is_object($value)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] is an object and not a string');
        }

        $value = strval($value);

        if ($trim) {
            $value = trim($value);
        }

        if ((!$allowEmpty) && ($value === '')) {
            throw new Exception('Parameter [' . $propertyName . '] empty value not allowed');
        }

        $object->{$propertyName} = $value;
    }

    /**
     * Check if variable is an array
     * @param object $object Variable
     * @param string $propertyName Variable
     */
    protected function checkArray(&$object, $propertyName)
    {
        if (!property_exists($object, $propertyName)) {
            throw new Exception('Property [' . $propertyName . '] not defined');
        }
        $value = $object->{$propertyName};

        if (!is_array($value)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] is not an array');
        }

        $object->{$propertyName} = $value;
    }

    /**
     * Check if variable is an object
     * @param object $object Variable
     * @param string $propertyName Variable
     */
    protected function checkObject(&$object, $propertyName)
    {
        if (!property_exists($object, $propertyName)) {
            throw new Exception('Property [' . $propertyName . '] not defined');
        }
        $value = $object->{$propertyName};

        if (!is_object($value)) {
            throw new Exception('Parameter [' . $propertyName . '] value [' . $value . '] is not an object');
        }

        $object->{$propertyName} = $value;
    }

    /**
     * Check if variable is a boolean
     * @param object $object Variable
     * @param string $propertyName Variable
     */
    protected function checkBoolean(&$object, $propertyName)
    {
        if (!property_exists($object, $propertyName)) {
            throw new Exception('Property [' . $propertyName . '] not defined');
        }
        $value = $object->{$propertyName};

        if (! (($value === false) || ($value === true))) {
            throw new Exception('Property [' . $propertyName . '] is not a boolean value');
        }

        $object->{$propertyName} = $value;
    }

}
