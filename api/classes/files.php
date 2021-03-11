<?php

require_once __DIR__ . '/../apiBaseClass.php';

class files extends apiBaseClass
{

    /**
     * Check current working directory from browser
     * @param string $data Parameters
     * @return array with result
     */
    public function checkDirectory($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'directory', true, true);

        //
        $result = true;
        $dir = '';
        try {

            // Base directory on server
            $baseDir = $this->config->get('base', 'server.absPath');

            // Append current working directory from browser to base directory
            $dir = $baseDir . '/' . trim($data->directory, '/');

            // Resolve ..
            $dir = realpath($dir);
            if ($dir === false) {
                throw new Exception('Path [' . $data->directory . '] not valid');
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
     * @param string $data Parameters
     * @return array with result
     */
    public function filterFilename($filename)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

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

    /**
     * Create directory
     * @param string $data Parameters
     * @return array with result
     */
    public function directoryCreate($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'currentPath', true, true);
        $this->checkString($data, 'directory', true, false);

        // Permission
        $permission = octdec( // has to be octal value
            $this->config->get(
                'base',
                'site.permissions.directory',
                '0755' // default permission
            )
        );

        // Check if directory is valid
        $dir = $this->checkDirectory((object)array(
            'directory' => $data->currentPath
        ));
        if ($dir['isValid'] === true) {

            // Create directory
            $newDir = $dir['absPath'] . '/' . $this->filterFilename($data->directory);
            if (mkdir(
                    $newDir,
                    $permission,
                    false
                ) !== true) {
                $result = 'Directory [' . $newDir . '] creation failed';
            } else {
                $result = true;
            }

        } else {
            $result = $dir['isValid'];
        }

        return array(
            'directoryCreate' => $result,
            'permission' => $permission
        );
    }

    /**
     * Delete directory
     * @param string $data Parameters
     * @return array with result
     */
    public function directoryDelete($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'currentPath', true, true);
        $this->checkString($data, 'directory', true, false);

        //
        $deleteDir = $data->currentPath . '/' . $this->filterFilename($data->directory);
        $dir = $this->checkDirectory((object)array(
            'directory' => $deleteDir
        ));
        if ($dir['isValid'] !== true) {
            $result = $dir['isValid'];
        } else {
            $content = glob($dir['absPath'] . '/*');
            if (count($content) > 0) {
                $result = 'Directory is not empty [' . $dir['absPath'] . '] found [' . count($content) . '] elements';
            } else if (rmdir(
                    $dir['absPath']
                ) !== true) {
                $result = 'Directory [' . $dir['absPath'] . '] deletion failed';
            } else {
                $result = true;
            }
        }

        return array(
            'directoryDelete' => $result
        );
    }

    /**
     * Create file
     * @param string $data Parameters
     * @return array with result
     */
    public function fileCreate($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'file', true, false);
        $this->checkString($data, 'currentPath', true, true);

        // Permission
        $permission = octdec( // has to be octal value
            $this->config->get(
                'base',
                'site.permissions.file',
                '0644' // default permission
            )
        );

        // Check if directory is valid
        $dir = $this->checkDirectory((object)array(
            'directory' => $data->currentPath
        ));

        // Create file
        $createFile = $dir['absPath'] . '/' . $this->filterFilename($data->file);
        if ($dir['isValid'] !== true) {
            $result = $dir['isValid'];
        } else if (file_exists($createFile)) {
            $result = 'File [' . $this->filterFilename($data->file) . '] already exist in directory [' . $dir['absPath'] . ']';
        } else if (file_put_contents(
                $createFile,
                ''
            ) === false) {
            $result = 'File [' . $createFile . '] creation in directory [' . $data->currentPath . '] failed';
        } else {
            chmod($createFile, $permission);
            $result = true;
        }

        return array(
            'fileCreate' => $result
        );
    }

    /**
     * Delete file
     * @param string $data Parameters
     * @return array with result
     */
    public function fileDelete($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'currentPath', true, true);
        $this->checkString($data, 'file', true, false);

        //
        $dir = $this->checkDirectory((object)array(
            'directory' => $data->currentPath
        ));
        $deleteFile = $dir['absPath'] . '/' . $this->filterFilename($data->file);
        if ($dir['isValid'] !== true) {
            $result = $dir['isValid'];
        } else if (!file_exists($deleteFile)) {
            $result = 'File [' . $this->filterFilename($data->file) . '] does not exist in directory [' . $dir['absPath'] . ']';
        } else if (unlink(
                $deleteFile
            ) !== true) {
            $result = 'File [' . $deleteFile . '] deletion failed';
        } else {
            $result = true;
        }

        return array(
            'fileDelete' => $result
        );
    }

    /**
     * Load file
     * @param string $data Parameters
     * @return array with result
     */
    public function fileLoad($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'currentPath', true, true);
        $this->checkString($data, 'file', true, false);

        //
        $dir = $this->checkDirectory((object)array(
            'directory' => $data->currentPath
        ));
        $loadFile = $dir['absPath'] . '/' . $this->filterFilename($data->file);
        $result = false;
        $content = '';
        if ($dir['isValid'] !== true) {
            $result = $dir['isValid'];
        } else if (!file_exists($loadFile)) {
            $result = 'File [' . $this->filterFilename($data->file) . '] does not exist in directory [' . $dir['absPath'] . ']';
        } else {
            $content = file_get_contents($loadFile);
            if ($content === false) {
                $result = 'Loading file [' . $loadFile . '] failed';
            } else {
                $result = true;
            }
        }

        return array(
            'fileLoad' => $result,
            'fileContent' => $content
        );
    }

    /**
     * Save file
     * @param string $data Parameters
     * @return array with result
     */
    public function fileSave($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'currentPath', true, true);
        $this->checkString($data, 'file', true, false);

        //
        $dir = $this->checkDirectory((object)array(
            'directory' => $data->currentPath
        ));
        $loadFile = $dir['absPath'] . '/' . $this->filterFilename($data->file);
        if ($dir['isValid'] !== true) {
            $result = $dir['isValid'];
        } else if (!file_exists($loadFile)) {
            $result = 'File [' . $this->filterFilename($data->file) . '] does not exist in directory [' . $dir['absPath'] . ']';
        } else if (file_put_contents($loadFile, $data->content) === false) {
            $result = 'File [' . $loadFile . '] save operation failed';
        } else {
            $result = true;
        }

        return array(
            'fileSave' => $result
        );
    }

}