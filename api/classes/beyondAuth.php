<?php

include_once __DIR__ . '/../apiBaseClass.php';

class beyondAuth extends beyondApiBaseClass
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
        if (($queryResult === false) || (! ($row = $queryResult->fetch()))) {
            $valid = 'System failure';
        } else if ($row['userName'] !== $data->userName) {
            $valid = 'Wrong user';
        } else if (!password_verify($data->password, $row['password'])) {
            $valid = 'Wrong password';
        } else {
            $valid = true;

            // Mark session as logged in
            $_SESSION[$this->prefix . 'data']['auth'] = array(
                'userName' => $row['userName'],
                'roles' => $row['roles']
            );
        }

        return array(
            'login' => $valid
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