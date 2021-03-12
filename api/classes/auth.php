<?php

require_once __DIR__ . '/../apiBaseClass.php';

class auth extends beyondApiBaseClass
{

    /**
     * Login
     * @param object $data Parameters
     * @return array with result
     */
    public function login($data)
    {
        // No permission check - Public API call

        // Check user input
        $this->checkString($data, 'userName', true, false);
        $this->checkString($data, 'password', true, false);

        // Close active session
        $_SESSION[$this->prefix . 'data'] = array();

        // Query user database
        $queryResult = $this->db->defaultDatabase->select(
            $this->prefix . 'users',
            array('*'),
            array(
                'userName = \'' . $this->db->defaultDatabase->escape($data->userName) . '\''
            )
        );

        // Check if the password is valid
        $valid = false;
        if (($queryResult !== false) && ($row = $queryResult->fetch())) {
            if (($row['userName'] == $data->userName) && (password_verify($data->password, $row['password']))) {
                $valid = true;

                // Mark session as logged in
                $_SESSION[$this->prefix . 'data']['auth'] = array(
                    'userName' => $row['userName'],
                    'roles' => $row['roles']
                );
            }
        }

        return array(
            'loginValid' => $valid,
            'session' => $_SESSION,
            'prefix' => $this->prefix,
            'sessionID' => session_id(),
            'sessionName' => session_name(),
        );
    }

    /**
     * Logout
     * @param object $data Parameters
     * @return array with result
     */
    public function logout($data)
    {
        // No permission check - Public API call

        //
        $_SESSION[$this->prefix . 'data'] = array();

        return array(
            'logoutDone' => true
        );
    }

}