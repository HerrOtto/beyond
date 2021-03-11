<?php

/**
 * Output exception message
 * @author     Tim David Saxen <info@netzmal.de>
 */
class exceptionHandler
{

    private $exceptions = array();
    private $config = null;

    /**
     * Constructor
     * @param object $e Pointer to config object
     */
    function __construct(&$config)
    {
        $this->config = $config;
    }

    /**
     * Add exception to stack
     * @param string $e Exception
     */
    public function add($e)
    {
        array_push($this->exceptions, array(
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ));
    }

    /**
     * Output exception stack as HTML
     * @return string HTML formated exception messages
     */
    public function html()
    {
        $result = false;
        foreach ($this->exceptions as $exceptionIndex => $exceptionItem) {
            if ($result !== false) {
                $result .= "<br>" . PHP_EOL;
            } else {
                $result = "";
            }
            $result .=
                'Exception: ' . $exceptionItem['message'] . '<br>' . PHP_EOL .
                ($this->config->get('base', 'site.debug') !== true ?
                    '' :
                    'Trace:<br>' . PHP_EOL .
                    nl2br($exceptionItem['trace']) . PHP_EOL
                );
        }
        return $result === false ? false : htmlentities($result);
    }

    /**
     * Output exception stack as JSON
     * @return string JSON formated exception messages
     */
    public function json()
    {
        $result = array();
        foreach ($this->exceptions as $exceptionIndex => $exceptionItem) {
            array_push($result, array(
                'message' => $exceptionItem['message'],
                'trace' => ($this->config->get('base', 'site.debug') !== true ? array() : explode(PHP_EOL, $exceptionItem['trace']))
            ));
        }
        return count($result) < 1 ? false : json_encode($result);
    }

    /**
     * Output exception stack as Array
     * @return string JSON formated exception messages
     */
    public function arr()
    {
        $result = array();
        foreach ($this->exceptions as $exceptionIndex => $exceptionItem) {
            array_push($result, array(
                'message' => $exceptionItem['message'],
                'trace' => ($this->config->get('base', 'site.debug') !== true ? array() : explode(PHP_EOL, $exceptionItem['trace']))
            ));
        }
        return count($result) < 1 ? false : $result;
    }

}