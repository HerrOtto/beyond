<?php

/**
 * Global used functions
 * @author     Tim David Saxen <info@netzmal.de>
 */
class beyondTools
{

    private string $prefix = '';
    private beyondConfig $config;

    /**
     * Constructor
     * @param string $prefix Prefix for this instance of beyond
     * @param object $config Pointer to configuration object
     * @return mixed
     */
    public function __construct($prefix, &$config)
    {
        $this->prefix = $prefix;
        $this->config = $config;
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
     * Get current user name
     * @result mixed Returns the current username if logged in otherwise "false"
     */

    public function currentUser()
    {
        $result = false;
        if (array_key_exists('auth', $_SESSION[$this->prefix . 'data'])) {
            if (array_key_exists('roles', $_SESSION[$this->prefix . 'data']['auth'])) {
                $result = $_SESSION[$this->prefix . 'data']['auth']['userName'];
            }
        }
        return $result;
    }

    /**
     * Check current working directory from browser
     * @param string $directory Directory to check
     * @return array with result
     */
    public function checkDirectory($directory)
    {
        //
        $result = true;
        $directory = trim($directory);
        $dir = '';
        try {

            // Base directory on server
            $baseDir = $this->config->get('base', 'server.absPath');

            // Append current working directory from browser to base directory
            $dir = $baseDir . '/' . trim($directory, '/');

            // Resolve ..
            $dir = realpath($dir);
            if ($dir === false) {
                throw new Exception('Path [' . $directory . '] not valid');
            }

            // Check if the user wants to break out the base directory
            if (substr($dir, 0, strlen($baseDir)) !== $baseDir) {
                throw new Exception('Path [' . $dir . '] out of base directory [' . $baseDir . ']');
            }

            // Check if directory exists
            if (!is_dir($dir)) {
                throw new Exception('Path [' . $dir . '] does not exist');
            }

        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        return array(
            'isValid' => $result,
            'absPath' => rtrim($dir, '/'),
            'relPath' => $dir != '' ? trim(substr($dir, strlen($baseDir) + 1), '/') : ''
        );
    }

    /**
     * Cleanup filename
     * @param string $filename File name to cleanup
     * @return string Clean file name
     */
    public function filterFilename($filename)
    {
        // Remove illegal file system characters
        $filename = str_replace(
            array_merge(
                array_map('chr', range(0, 31)),
                array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
            ),
            '',
            $filename
        );

        // Remove two dots at the beginning
        $filename = preg_replace(
            '/^\.\./',
            '',
            $filename
        );

        // maximise filename length to 255 bytes
        //http://serverfault.com/a/9548/44086
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');

        return $filename;
    }

    /*
     * HTTP GET request
     *
     * @param string $url URL
     * @param string $outputFile File name where to put http output
     * @param string $timeoutSec Timeout value in seconds
     * @result string Result if $outputFile is defined this function returns true
     */

    public function httpGet($url, $outputFile = false, $timeoutSec = 10)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeoutSec);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeoutSec);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if ($outputFile !== false) {
            $fp = fopen($outputFile, 'w+');
            curl_setopt($curl, CURLOPT_FILE, $fp);
        }

        $result = curl_exec($curl);

        if ($outputFile !== false) {
            fclose($fp);
        }

        if (curl_error($curl)) {
            throw new Exception(curl_error($curl));
        };

        curl_close($curl);

        if ($outputFile === false) {
            return $result;
        } else {
            return true;
        }
    }

}
