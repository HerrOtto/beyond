<?php

require_once __DIR__ . '/../apiBaseClass.php';

class users extends apiBaseClass
{

    /**
     * Internal function to cleanup user roles
     * @param string $roles User roles (comma separated)
     * @return string Clean user roles (comma separated)
     */
    private function cleanupRoles($roles) {

        // Split roles to array of roles
        $roles = str_replace(' ', '', $roles);
        $rolesArray = explode(',', $roles);
        $rolesArray = array_filter($rolesArray);

        // Check if ther is an "admin" and a "view" role (Remove "view")
        if ((in_array('admin', $rolesArray)) && (in_array('view', $rolesArray))) {
            $rolesArray = array_values(array_diff($rolesArray, array('view')));
        }

        return implode(',', $rolesArray);
    }

    /**
     * Internal function: Check if user roles contain "admin" role
     * @param string $roles User roles (comma separated)
     * @return string true if the user roles contain "admin" role otherwise false
     */
    private function hasAdminRole($roles) {

        // Split roles to array of roles
        $roles = str_replace(' ', '', $roles);
        $rolesArray = explode(',', $roles);

        return in_array('admin', $rolesArray);
    }

    /**
     * Internal function: Check password
     * @param string $password1 Password from user
     * @param string $password2 Again password from user
     */
    private function checkPassword($password1, $password2)
    {
        if ($password1 !== $password2) {
            throw new Exception('Password does not match confirmation');
        }

        if ($password1 === '') {
            throw new Exception('Empty password not allowed');
        }

        if (strlen($password1) > 100) {
            throw new Exception('Password too long: The maximum password length is 100 characters');
        }

        if (strlen($password1) < 10) {
            throw new Exception('Password too short: The minimum password length is 10 characters');
        }

        if (!preg_match('/[A-Z]/', $password1)) {
            throw new Exception('Password should include at least one upper case letter');
        }

        if (!preg_match('/[a-z]/', $password1)) {
            throw new Exception('Password should include at least one lower case letter');
        }

        if (!preg_match('/[0-9]/', $password1)) {
            throw new Exception('Password should include at least one number');
        }
    }

    /**
     * Fetch user list
     * @param string $data Parameters
     * @return array with result
     */
    public function fetch($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // No user input

        //
        $users = array();

        // Query user database
        $queryResult = $this->db->defaultDatabase->select(
            $this->prefix . 'users',
            array('userName', 'roles'),
            array()
        );
        if ($queryResult !== false) {
            while ($row = $queryResult->fetch()) {
                $users[$row['userName']] = array(
                    'roles' => $row['roles']
                );
            }
        }

        return array(
            'fetch' => $users
        );

    }

    /**
     * Add user
     * @param string $data Parameters
     * @return array with result
     */
    public function add($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'userName', true, false);
        $this->checkString($data, 'roles', true, true);
        $this->checkString($data, 'password1', true, false);
        $this->checkString($data, 'password2', true, false);

        // Check password
        $this->checkPassword($data->password1, $data->password2);

        // Cleanup roles
        $roles = $this->cleanupRoles($data->roles);

        // Add user to database
        $result = $this->db->defaultDatabase->insert(
            $this->prefix . 'users',
            array(
                'userName' => $data->userName,
                'roles' => $roles,
                'password' => password_hash($data->password1, PASSWORD_DEFAULT, array('cost' => 11))
            )
        );

        return array(
            'add' => $result
        );

    }

    /**
     * Delete user
     * @param string $data Parameters
     * @return array with result
     */
    public function delete($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'userName', true, false);

        // Fetch all current users with role "admin"
        $admins = array();
        $queryResult = $this->db->defaultDatabase->select(
            $this->prefix . 'users',
            array('userName', 'roles'),
            array()
        );
        if ($queryResult !== false) {
            while ($row = $queryResult->fetch()) {
                if (in_array('admin', explode(' ', $row['roles']))) {
                    array_push($admins, $row['userName']);
                }
            }
        }

        // Prohibit deletion of last admin
        if ((count($admins) == 1) && ($admins[0] === $data->userName)) {
            throw new Exception('You are not allowed to delete last user [' . $data->userName . '] with role "admin"');
        }

        // Remove user from database
        $result = $this->db->defaultDatabase->delete(
            $this->prefix . 'users',
            array(
                'userName = \'' . $this->db->defaultDatabase->escape($data->userName) . '\''
            )
        );

        return array(
            'delete' => $result
        );

    }

    /**
     * Modify user
     * @param string $data Parameters
     * @return array with result
     */
    public function modify($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'userName', true, false);
        $this->checkString($data, 'roles', true, true);

        // If password change is requested, check validity
        $this->checkString($data, 'password1', true, true);
        if ($data->password1 !== '') {
            $this->checkString($data, 'password2', true, false);
            $this->checkPassword($data->password1, $data->password2);
        }

        // Cleanup roles
        $roles = $this->cleanupRoles($data->roles);

        // Check if the user is an admin
        $hasAdmin = $this->hasAdminRole($roles);

        // Fetch all current users with role "admin"
        $admins = array();
        $queryResult = $this->db->defaultDatabase->select(
            $this->prefix . 'users',
            array('userName', 'roles'),
            array()
        );
        if ($queryResult !== false) {
            while ($row = $queryResult->fetch()) {
                if (in_array('admin', explode(' ', $row['roles']))) {
                    array_push($admins, $row['userName']);
                }
            }
        }

        // Prohibit deletion of last admin
        if ((count($admins) == 1) && ($admins[0] === $data->userName) && ($hasAdmin === false)) {
            throw new Exception('You are not allowed to remove last existing "admin" role from user [' . $data->userName . ']');
        }

        // Modify user in database
        if ($data->password1 !== '') {
            $result = $this->db->defaultDatabase->update(
                $this->prefix . 'users',
                array(
                    'roles' => $roles,
                    'password' => $data->password1
                ),
                array(
                    'userName = \'' . $this->db->defaultDatabase->escape($data->userName) . '\''
                )
            );
        } else {
            $result = $this->db->defaultDatabase->update(
                $this->prefix . 'users',
                array(
                    'roles' => $roles
                ),
                array(
                    'userName = \'' . $this->db->defaultDatabase->escape($data->userName) . '\''
                )
            );
        }

        return array(
            'modify' => $result
        );

    }

}