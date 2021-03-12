<?php

require_once __DIR__ . '/../../../api/apiBaseClass.php';
require_once __DIR__ . '/../inc/class_contentDatabase.php';

class content_config extends beyondApiBaseClass
{

    /**
     * Load configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function load($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // No user input

        //
        $configJson = file_get_contents(__DIR__ . '/../../../config/content_settings.json');
        if (trim($configJson) === '') {
            $configObj = new stdClass();
        } else {
            $configObj = json_decode($configJson);
        }

        if (!property_exists($configObj, 'version')) {
            $configObj->version = 1;
        }

        if (!property_exists($configObj, 'database')) {
            $configObj->database = $this->config->get('database', 'defaultDatabase');
        }

        return array(
            'load' => $configObj
        );
    }

    /**
     * Save configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function save($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'database', true, false);

        // Does this Database exists?
        if (!array_key_exists($data->database, $this->db->databases)) {
            throw new Exception('Unknown database [' . $data->database . '] selected');
        }

        // Database changed?
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        if ($configObj->database !== $data->database) {

            // Current Database
            $databaseCurrent = $this->db->databases[$configObj->database];

            // Load settins from current database
            $query = $databaseCurrent->select(
                $this->prefix . 'content_settings',
                array(
                    'filePathName',
                    'configJson'
                ),
                array()
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'content_settings]');
            }
            $settings = array();
            if ($row = $query->fetch()) {
                array_push($settings, array(
                    'filePathName' => $row['filePathName'],
                    'configJson' => $row['configJson']
                ));
            }

            // Load data from current database
            $query = $databaseCurrent->select(
                $this->prefix . 'content_data',
                array(
                    'filePathName',
                    'dataJson'
                ),
                array()
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'content_data]');
            }
            $content = array();
            if ($row = $query->fetch()) {
                array_push($content, array(
                    'filePathName' => $row['filePathName'],
                    'dataJson' => $row['dataJson']
                ));
            }

            $databaseNew = $this->db->databases[$data->database];
            $contentDatabase = new class_contentDatabase($this->prefix);
            $contentDatabase->init($databaseNew);

            // From now on: Rollback on failure
            try {

                // Write settings to new database
                foreach ($settings as $settingsItem) {
                    $query = $databaseNew->insert(
                        $this->prefix . 'content_settings',
                        array(
                            'filePathName' => $settingsItem['filePathName'],
                            'configJson' => $settingsItem['configJson']
                        )
                    );
                    if ($query === false) {
                        throw new Exception('Can not insert into table [' . $this->prefix . 'content_settings]');
                    }
                }

                // Write data to new database
                foreach ($content as $contentItem) {
                    $query = $databaseNew->insert(
                        $this->prefix . 'content_data',
                        array(
                            'filePathName' => $contentItem['filePathName'],
                            'dataJson' => $contentItem['dataJson']
                        )
                    );
                    if ($query === false) {
                        throw new Exception('Can not insert into table [' . $this->prefix . 'content_data]');
                    }
                }

            } catch (Exception $e) {

                // Rollback
                $contentDatabase->drop($databaseNew);

                // Replay exception
                throw new Exception($e->getMessage());

            }

            // Drop old database
            $contentDatabase->drop($databaseCurrent);

        }

        // Change database in configuration
        $configObj->database = $data->database;

        file_put_contents(__DIR__ . '/../../../config/content_settings.json', json_encode($configObj, JSON_PRETTY_PRINT));

        return array(
            'save' => true
        );
    }

    /**
     * Get file config
     * @param string $pathAndFilename Filename with path
     * @return object Configuration
     */
    private function getFileConfig($pathAndFilename)
    {
        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Getting file configuration from database
        $query = $database->select(
            $this->prefix . 'content_settings',
            array(
                '*'
            ),
            array(
                'filePathName = \'' . $database->escape($pathAndFilename) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'content_settings]');
        }

        $fileConfig = false;
        if ($row = $query->fetch()) {
            $fileConfig = $row['configJson'];
        }

        // New configuration
        if ($fileConfig === false) {
            $fileConfigObj = new stdClass();
            $fileConfigObj->version = 1;
            $fileConfigObj->fields = array();
        } else {
            $fileConfigObj = json_decode($fileConfig);
            if (!property_exists($fileConfigObj, 'version')) {
                $fileConfigObj->version = 1;
            }
            if (!property_exists($fileConfigObj, 'fields')) {
                $fileConfigObj->fields = array();
            }
            $fileConfigObj->fields = (array)$fileConfigObj->fields; // Json Object to Array
        }

        return $fileConfigObj;
    }

    /**
     * Get file data
     * @param string $pathAndFilename Filename with path
     * @return object Content data
     */
    private function getFileData($pathAndFilename)
    {
        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Getting file configuration from database
        $query = $database->select(
            $this->prefix . 'content_data',
            array(
                '*'
            ),
            array(
                'filePathName = \'' . $database->escape($pathAndFilename) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'content_data]');
        }

        $fileData = false;
        if ($row = $query->fetch()) {
            $fileData = $row['dataJson'];
        }

        // New configuration
        if ($fileData === false) {
            $fileDataObj = new stdClass();
            $fileDataObj->version = 1;
            $fileDataObj->fields = array();
        } else {
            $fileDataObj = json_decode($fileData);
            if (!property_exists($fileData, 'version')) {
                $fileDataObj->version = 1;
            }
            if (!property_exists($fileDataObj, 'fields')) {
                $fileDataObj->fields = new stdClass();
            }
        }

        return $fileDataObj;
    }

    /**
     * Write file config
     * @param string $pathAndFilename Filename with path
     * @return object Configuration
     */
    private function setFileConfig($pathAndFilename, $fileConfigObj)
    {
        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Check existence of field configuration
        $query = $database->select(
            $this->prefix . 'content_settings',
            array(
                '*'
            ),
            array(
                'filePathName = \'' . $database->escape($pathAndFilename) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'content_settings]');
        }
        $fileConfig = false;
        if ($row = $query->fetch()) {
            $fileConfig = $row['configJson'];
        }

        // Write
        if ($fileConfig === false) {
            // Adding file configuration
            $query = $database->insert(
                $this->prefix . 'content_settings',
                array(
                    'filePathName' => $pathAndFilename,
                    'configJson' => json_encode($fileConfigObj)
                )
            );
            if ($query === false) {
                throw new Exception('Can not update table [' . $this->prefix . 'content_settings]');
            }
        } else {
            // Updating file configuration
            $query = $database->update(
                $this->prefix . 'content_settings',
                array(
                    'configJson' => json_encode($fileConfigObj)
                ),
                array(
                    'filePathName = \'' . $database->escape($pathAndFilename) . '\''
                )
            );
            if ($query === false) {
                throw new Exception('Can not update table [' . $this->prefix . 'content_settings]');
            }
        }

        return true;
    }

    /**
     * Add field
     * @param string $data Parameters
     * @return array with result
     */
    public function addField($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'fileName', true, false);
        $this->checkString($data, 'path', true, true);
        $this->checkString($data, 'fieldName', true, false);
        $this->checkString($data, 'fieldKind', true, false);

        if (!in_array($data->fieldKind, array('string', 'longtext', 'html'))) {
            throw new Exception('Unknown field type [' . $data->fieldKind . ']');
        }

        $data->fieldName = preg_replace('/[^a-zA-Z0-9]/', '', $data->fieldName);
        if ($data->fieldName === '') {
            throw new Exception('Empty field name not allowed');
        }

        // Check file and directory
        $dir = $this->tools->checkDirectory($data->path);
        if (!$dir['isValid']) {
            throw new Exception('Invalid directory [' . $data->$data->path . ']');
        }
        $data->fileName = $this->tools->filterFilename($data->fileName);
        if (!file_exists($this->config->get('base', 'server.absPath') . '/' . $dir['relPath'] . '/' . $data->fileName)) {
            throw new Exception('Unknown file [' . $dir['relPath'] . '/' . $data->fileName . ']');
        }

        // Load file configuration
        $fileConfigObj = $this->getFileConfig($dir['relPath'] . '/' . $data->fileName);

        // Check if file exists
        if (array_key_exists($data->fieldName, $fileConfigObj->fields)) {
            throw new Exception('A field with the name [' . $data->fieldName . '] already exists');
        }

        // Add field
        $fileConfigObj->fields[$data->fieldName] = array(
            'kind' => $data->fieldKind
        );

        // Save new configuration to database
        $this->setFileConfig($dir['relPath'] . '/' . $data->fileName, $fileConfigObj);

        return array(
            'addField' => $data->fieldName,
            'config' => $fileConfigObj
        );
    }

    /**
     * Remove field
     * @param string $data Parameters
     * @return array with result
     */
    public function removeField($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'fileName', true, false);
        $this->checkString($data, 'path', true, true);
        $this->checkString($data, 'fieldName', true, false);#

        // Check file and directory
        $dir = $this->tools->checkDirectory($data->path);
        if (!$dir['isValid']) {
            throw new Exception('Invalid directory [' . $data->$data->path . ']');
        }
        $data->fileName = $this->tools->filterFilename($data->fileName);
        if (!file_exists($this->config->get('base', 'server.absPath') . '/' . $dir['relPath'] . '/' . $data->fileName)) {
            throw new Exception('Unknown file [' . $dir['relPath'] . '/' . $data->fileName . ']');
        }

        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Load file configuration
        $fileConfigObj = $this->getFileConfig($dir['relPath'] . '/' . $data->fileName);

        // Remove field
        if (array_key_exists($data->fieldName, $fileConfigObj->fields)) {
            unset($fileConfigObj->fields[$data->fieldName]);
        } else {
            throw new Exception('Unknown field name [' . $data->fieldName . '] not removed');
        }

        // Delete field content from DB
        $query = $database->delete(
            $this->prefix . 'content_data',
            array(
                'filePathName = \'' . $database->escape($dir['relPath'] . '/' . $data->fileName) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not delete from table [' . $this->prefix . 'content_data]');
        }

        // Save new configuration to database
        $this->setFileConfig($dir['relPath'] . '/' . $data->fileName, $fileConfigObj);

        return array(
            'removeField' => true,
            'config' => $fileConfigObj
        );
    }


    /**
     * Load fields
     * @param string $data Parameters
     * @return array with result
     */
    public function loadFields($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin,view') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'fileName', true, false);
        $this->checkString($data, 'path', true, true);

        // Check file and directory
        $dir = $this->tools->checkDirectory($data->path);
        if (!$dir['isValid']) {
            throw new Exception('Invalid directory [' . $data->$data->path . ']');
        }
        $data->fileName = $this->tools->filterFilename($data->fileName);
        if (!file_exists($this->config->get('base', 'server.absPath') . '/' . $dir['relPath'] . '/' . $data->fileName)) {
            throw new Exception('Unknown file [' . $dir['relPath'] . '/' . $data->fileName . ']');
        }

        // Load file configuration
        $fileConfigObj = $this->getFileConfig($dir['relPath'] . '/' . $data->fileName);

        // Load data
        $dataContentObj = $this->getFileData($dir['relPath'] . '/' . $data->fileName);

        return array(
            'loadFields' => true,
            'config' => $fileConfigObj,
            'content' => $dataContentObj
        );
    }

    /**
     * Save data
     * @param string $data Parameters
     * @return array with result
     */
    public function dataSave($data)
    {
        // Check permissions
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkString($data, 'fileName', true, false);
        $this->checkString($data, 'path', true, true);
        $this->checkObject($data, 'content');

        // Check file and directory
        $dir = $this->tools->checkDirectory($data->path);
        if (!$dir['isValid']) {
            throw new Exception('Invalid directory [' . $data->$data->path . ']');
        }
        $data->fileName = $this->tools->filterFilename($data->fileName);
        if (!file_exists($this->config->get('base', 'server.absPath') . '/' . $dir['relPath'] . '/' . $data->fileName)) {
            throw new Exception('Unknown file [' . $dir['relPath'] . '/' . $data->fileName . ']');
        }

        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Load file configuration
        $fileConfigObj = $this->getFileConfig($dir['relPath'] . '/' . $data->fileName);

        // Copy data into new Object
        $newConfigObj = new stdClass();
        $newConfigObj->version = 1;
        $newConfigObj->fields = new stdClass();
        foreach ($fileConfigObj->fields as $fieldName => $fieldConfig) {
            $newConfigObj->fields->{$fieldName} = new stdClass();
            foreach ($this->languages as $language => $languageText) {
                if (property_exists($data->content, $fieldName)) {
                    if (property_exists($data->content->{$fieldName}, $language)) {
                        $newConfigObj->fields->{$fieldName}->{$language} = $data->content->{$fieldName}->{$language};
                    }
                } else {
                    $newConfigObj->fields->{$fieldName}->{$language} = '';
                }
            }
        }

        // Check existence of field configuration
        $query = $database->select(
            $this->prefix . 'content_data',
            array(
                '*'
            ),
            array(
                'filePathName = \'' . $database->escape($dir['relPath'] . '/' . $data->fileName) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'content_data]');
        }
        $fileConfig = false;
        if ($row = $query->fetch()) {
            $fileConfig = $row['dataJson'];
        }

        // Write
        if ($fileConfig === false) {
            // Adding file configuration
            $query = $database->insert(
                $this->prefix . 'content_data',
                array(
                    'filePathName' => $dir['relPath'] . '/' . $data->fileName,
                    'dataJson' => json_encode($newConfigObj)
                )
            );
            if ($query === false) {
                throw new Exception('Can not update table [' . $this->prefix . 'content_data]');
            }
        } else {
            // Updating file configuration
            $query = $database->update(
                $this->prefix . 'content_data',
                array(
                    'dataJson' => json_encode($newConfigObj)
                ),
                array(
                    'filePathName = \'' . $database->escape($dir['relPath'] . '/' . $data->fileName) . '\''
                )
            );
            if ($query === false) {
                throw new Exception('Can not update table [' . $this->prefix . 'content_data]');
            }
        }

        // Save data to database
        // $this->setFileConfig($dir['relPath'] . '/' . $data->fileName, $fileConfigObj);

        return array(
            'dataSave' => true,
            'content' => $newConfigObj,
            'fileConfig' => $fileConfig
        );
    }

}