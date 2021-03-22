<?php

// API with underscore "_" at the beginning is not allowed due to plugin api naming scheme: pluginName_apiName

include_once __DIR__ . '/../apiBaseClass.php';

class beyondApis extends beyondApiBaseClass
{

    /**
     * Cleanup API name (check validity)
     * @param string $apiName Name to check
     * @return string Clean API name
     */
    private function filterApiName($apiName)
    {
        $apiName = preg_replace("/\.php$/", '', $apiName);
        $apiName = preg_replace("/[^A-Za-z]/", '', $apiName);
        $apiName = substr($apiName, 0, 50);

        return $apiName;
    }

    /**
     * Create API
     * @param object $data Parameters
     * @return array with result
     */
    public function apiCreate($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check input
        $this->checkString($data, 'apiName', true, false);

        //
        $apiName = $this->filterApiName($data->apiName);
        if ($apiName === '') {
            throw new Exception('Empty API name not allowed');
        }
        if (class_exists($apiName)) {
            throw new Exception('API class with name [' . $apiName . '] exists');
        }

        // Check if internal API with that name exists
        if (file_exists(__DIR__ . '/' . $apiName . '.php')) {
            throw new Exception('This API name [' . $apiName . '] is already in use');
        }

        // Create API file
        $createFile = __DIR__ . '/../../config/siteClasses/' . $apiName . '.php';
        if (file_exists($createFile)) {
            throw new Exception('API file [' . $createFile . '] already exist');
        } else if (file_put_contents($createFile,
                '<?php' . PHP_EOL .
                '' . PHP_EOL .
                'include_once __DIR__ . \'/../../api/apiBaseClass.php\';' . PHP_EOL .
                '' . PHP_EOL .
                'class ' . $apiName . ' extends beyondApiBaseClass' . PHP_EOL .
                '{' . PHP_EOL .
                '' . PHP_EOL .
                '  // Add your API calls here:' . PHP_EOL .
                '  //' . PHP_EOL .
                '  // public function test($data) {' . PHP_EOL .
                '  //' . PHP_EOL .
                '  //   if ($data->parameter === \'value\') {' . PHP_EOL .
                '  //' . PHP_EOL .
                '  //     $this->db' . PHP_EOL .
                '  //     $this->variable' . PHP_EOL .
                '  //     $this->config' . PHP_EOL .
                '  //     $this->prefix' . PHP_EOL .
                '  //' . PHP_EOL .
                '  //   }' . PHP_EOL .
                '  //' . PHP_EOL .
                '  //   return array(' . PHP_EOL .
                '  //     \'test1\' => \'result1\',' . PHP_EOL .
                '  //     \'test2\' => \'result2\'' . PHP_EOL .
                '  //   );' . PHP_EOL .
                '  //' . PHP_EOL .
                '  // }' . PHP_EOL .
                '' . PHP_EOL .
                '}' . PHP_EOL
            ) === false) {
            $result = 'File [' . $createFile . '] creation failed';
        } else {
            $result = true;
        }

        return array(
            'apiCreate' => $result
        );
    }

    /**
     * Delete API
     * @param object $data Parameters
     * @return array with result
     */
    public function apiDelete($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check input
        $this->checkString($data, 'apiName', true, false);

        //
        if (strpos($data->apiName, '_') !== false) {
            return array(
                'apiDelete' => 'Deletion of plugin API file not allowed'
            );
        }
        $apiName = $this->filterApiName($data->apiName);
        $deleteFile = __DIR__ . '/../../config/siteClasses/' . $apiName . '.php';

        if (in_array($apiName, $this->internalApiFiles)) {
            $result = 'API file [' . $deleteFile . '] is an internal file';
        } else if (!file_exists($deleteFile)) {
            $result = 'API file [' . $deleteFile . '] does not exist';
        } else if (unlink(
                $deleteFile
            ) !== true) {
            $result = 'File [' . $deleteFile . '] deletion failed';
        } else {
            $result = true;
        }

        return array(
            'apiDelete' => $result
        );
    }

    /**
     * Receive/Load API to view
     * @param object $data Parameters
     * @return array with result
     */
    public function apiLoad($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check input
        $this->checkString($data, 'apiName', true, false);
        $this->checkString($data, 'kind', true, false);

        //
        if (($data->kind === 'plugin') && (strpos($data->apiName, '_') !== false)) {
            $apiPluginClassArray = explode('_', $data->apiName, 2);
            $plugin = $apiPluginClassArray[0];
            $plugin = $this->filterApiName($plugin);
            $apiName = $apiPluginClassArray[1];
            $apiName = $this->filterApiName($apiName);
            $loadFile = __DIR__ . '/../../plugins/' . $plugin . '/apiClasses/' . $apiName . '.php';
        } else if ($data->kind === 'internal') {
            $apiName = $this->filterApiName($data->apiName);
            $plugin = false;
            $loadFile = __DIR__ . '/' . $apiName . '.php';
        } else if ($data->kind === 'site') {
            $apiName = $this->filterApiName($data->apiName);
            $plugin = false;
            $loadFile = __DIR__ . '/../../config/siteClasses/' . $apiName . '.php';
        } else {
            throw new Exception('API file [' . $data->apiName . '] of kind [' . $data->kind . '] not found');
        }

        if (!file_exists($loadFile)) {
            $result = 'API file [' . $loadFile . '] does not exist';
            $content = '';
        } else {
            $content = file_get_contents($loadFile);
            if ($content === false) {
                $content = '';
                $result = 'Loading API [' . $loadFile . '] failed';
            } else {
                $result = true;
            }
        }
        return array(
            'apiLoad' => $result,
            'apiContent' => $content,
            'internal' => in_array($apiName, $this->internalApiFiles),
            'plugin' => $plugin !== false
        );
    }

    /**
     * Save API
     * @param object $data Parameters
     * @return array with result
     */
    public function apiSave($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check input
        $this->checkString($data, 'apiName', true, false);
        $this->checkString($data, 'content', false, true);

        //
        if (strpos($data->apiName, '_') !== false) {
            return array(
                'apiDelete' => 'Saving of plugin API file not allowed'
            );
        }

        $apiName = $this->filterApiName($data->apiName);
        $saveFile = __DIR__ . '/../../config/siteClasses/' . $apiName . '.php';

        if (!file_exists($saveFile)) {
            $result = 'File [' . $saveFile . '] does not exist';
        } else if (file_put_contents($saveFile, $data->content) === false) {
            $result = 'File [' . $saveFile . '] save operation failed';
        } else {
            $result = true;
        }

        return array(
            'apiSave' => $result
        );
    }

}