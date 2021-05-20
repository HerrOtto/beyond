<?php

class captchaHandler
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
     * @param beyondTools $tools Global beyond tools
     * @param beyondConfig $config Configuration
     */
    public function __construct($language, $prefix, $tools, $config)
    {
        $this->language = $language;
        $this->prefix = $prefix;
        $this->tools = $tools;
        $this->config = $config;

        $this->cache = array();
    }

    /**
     * Check captcha
     * @param string $id ID of captcha to check
     * @param string $text User captcha input to check against captcha of $id
    */
    public function check($id, $text)
    {
        if (! array_key_exists('captcha', $_SESSION[$this->prefix . 'data'])) {
            return false;
        }

        if (! array_key_exists($id, $_SESSION[$this->prefix . 'data']['captcha'])) {
            return false;
        }

        if ($_SESSION[$this->prefix . 'data']['captcha'][$id] !== $text) {
            // Remove used captcha
            unset($_SESSION[$this->prefix . 'data']['captcha'][$id]);
            return false;
        }

        // Remove used captcha
        unset($_SESSION[$this->prefix . 'data']['captcha'][$id]);
        return true;
    }

}
