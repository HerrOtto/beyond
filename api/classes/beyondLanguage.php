<?php

include_once __DIR__ . '/../apiBaseClass.php';

class beyondLanguage extends beyondApiBaseClass
{

    /**
     * Change user language
     * @param string $data Parameters
     * @return array with result
     */
    public function setLanguage($data)
    {
        // No permission check - Public API call

        // Check user input
        $this->checkString($data, 'language', true, false);

        $unknown = false;
        if (array_key_exists($data->language, $this->languages)) {
            $_SESSION[$this->prefix . 'data']['language'] = $data->language;
        } else {
            $unknown = true;
        }

        return array(
            'setLanguage' => $_SESSION[$this->prefix . 'data']['language'],
            'unknownLanguage' => $unknown
        );
    }

    /**
     * Get current user language
     * @param string $data Parameters
     * @return array with result
     */
    public function getLanguage($data)
    {
        // No permission check - Public API call

        // No user input expected

        return array(
            'getLanguage' => $_SESSION[$this->prefix . 'data']['language']
        );
    }

}