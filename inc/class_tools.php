<?php

/**
 * Global used functions
 * @author     Tim David Saxen <info@netzmal.de>
 */
class tools
{

    private $prefix = '';

    // Constructor
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /*
     * Check if current user has the required role
     *
     * @param string $role Required role multiple roles can be transfered
     * @result mixed Returns first matching role as string if the user has the required role otherwise "false"
     */

    public function checkRole($anyOfThisRoles)
    {

        $anyOfThisRoles = str_replace(' ', '', $anyOfThisRoles);
        $anyOfThisRolesArray = explode(',', $anyOfThisRoles);

        if (array_key_exists('auth', $_SESSION[$this->prefix . 'data'])) {
            if (array_key_exists('roles', $_SESSION[$this->prefix . 'data']['auth'])) {
                $roles = $_SESSION[$this->prefix . 'data']['auth']['roles'];
                $roles = str_replace(' ', '', $roles);
                $rolesArray = explode(',', $roles);

                foreach ($anyOfThisRolesArray as $checkRole) {
                    if (in_array($checkRole, $rolesArray)) {
                        return $checkRole;
                    }
                }
            }
        }

        return false;
    }

    /*
     * HTTP GET request
     *
     * @param string $url URL
     * @result string Result
     */

    public function httpGet($url, $timeoutSec = 10)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeoutSec);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeoutSec);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);

        if (curl_error($curl)) {
            throw new Exception(curl_error($curl));
        };

        curl_close($curl);

        return $result;
    }

}
