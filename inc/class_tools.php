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

}
