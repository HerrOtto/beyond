<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_seoDatabase.php';

class seo_config extends beyondApiBaseClass
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
        $configJson = file_get_contents(__DIR__ . '/../../../config/seo_settings.json');
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

        foreach ($this->languages as $lang => $langName) {
            if (!property_exists($configObj, 'defaults_' . $lang)) {
                $configObj->{'defaults_' . $lang} = new stdClass();
            }

            if (!property_exists($configObj->{'defaults_' . $lang}, 'titlePrefix')) {
                $configObj->{'defaults_' . $lang}->titlePrefix = '';
            }

            if (!property_exists($configObj->{'defaults_' . $lang}, 'titleSuffix')) {
                $configObj->{'defaults_' . $lang}->titleSuffix = ' | YOUR SITE';
            }

            if (!property_exists($configObj->{'defaults_' . $lang}, 'author')) {
                $configObj->{'defaults_' . $lang}->author = '';
            }

            if (!property_exists($configObj->{'defaults_' . $lang}, 'description')) {
                $configObj->{'defaults_' . $lang}->description = '';
            }

            if (!property_exists($configObj->{'defaults_' . $lang}, 'robots')) {
                $configObj->{'defaults_' . $lang}->robots = 'index,follow';
            }
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
        foreach ($this->languages as $lang => $langName) {
            $this->checkObject($data, 'defaults_' . $lang);
        }

        // Does this Database exists?
        if (!array_key_exists($data->database, $this->db->databases)) {
            throw new Exception('Unknown database [' . $data->database . '] selected');
        }

        // Load current configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));

        // Default values
        foreach ($this->languages as $lang => $langName) {
            if (!property_exists($data, 'defaults_' . $lang)) {
                $configObj->{'defaults_' . $lang} = new stdClass();
            }
            if (property_exists($data->{'defaults_' . $lang}, 'titlePrefix')) {
                $configObj->{'defaults_' . $lang}->titlePrefix = $data->{'defaults_' . $lang}->titlePrefix;
            }
            if (property_exists($data->{'defaults_' . $lang}, 'titleSuffix')) {
                $configObj->{'defaults_' . $lang}->titleSuffix = $data->{'defaults_' . $lang}->titleSuffix;
            }
            if (property_exists($data->{'defaults_' . $lang}, 'author')) {
                $configObj->{'defaults_' . $lang}->author = $data->{'defaults_' . $lang}->author;
            }
            if (property_exists($data->{'defaults_' . $lang}, 'description')) {
                $configObj->{'defaults_' . $lang}->description = $data->{'defaults_' . $lang}->description;
            }
            if (property_exists($data->{'defaults_' . $lang}, 'robots')) {
                $configObj->{'defaults_' . $lang}->robots = $data->{'defaults_' . $lang}->robots;
            }
        }

        // Database changed?
        if ($configObj->database !== $data->database) {

            // Current Database
            $databaseCurrent = $this->db->databases[$configObj->database];

            // Load data from current database
            $query = $databaseCurrent->select(
                $this->prefix . 'seo_data',
                array(
                    'filePathName',
                    'dataJson'
                ),
                array()
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'seo_data]');
            }
            $content = array();
            while ($row = $query->fetch()) {
                array_push($content, array(
                    'filePathName' => $row['filePathName'],
                    'dataJson' => $row['dataJson']
                ));
            }

            $databaseNew = $this->db->databases[$data->database];
            $seoDatabase = new seoDatabase($this->prefix);
            $seoDatabase->init($databaseNew);

            // From now on: Rollback on failure
            try {

                // Write data to new database
                foreach ($content as $contentItem) {
                    $query = $databaseNew->insert(
                        $this->prefix . 'seo_data',
                        array(
                            'filePathName' => $contentItem['filePathName'],
                            'dataJson' => $contentItem['dataJson']
                        )
                    );
                    if ($query === false) {
                        throw new Exception('Can not insert into table [' . $this->prefix . 'seo_data]');
                    }
                }

            } catch (Exception $e) {

                // Rollback
                $seoDatabase->drop($databaseNew);

                // Replay exception
                throw new Exception($e->getMessage());

            }

            // Drop old database
            $seoDatabase->drop($databaseCurrent);

        }

        // Change database in configuration
        $configObj->database = $data->database;

        file_put_contents(__DIR__ . '/../../../config/seo_settings.json', json_encode($configObj, JSON_PRETTY_PRINT));

        return array(
            'save' => true,
            'seo' => $configObj
        );
    }

    /**
     * Load file data
     * @param string $data Parameters
     * @return array with result
     */
    public function loadFileData($data)
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

        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Getting file configuration from database
        $query = $database->select(
            $this->prefix . 'seo_data',
            array(
                '*'
            ),
            array(
                'filePathName = \'' . ltrim($database->escape($dir['relPath'] . '/' . $data->fileName), '/') . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'seo_data]');
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

        return array(
            'loadFileData' => true,
            'fileData' => $fileDataObj,
            'config' => $configObj
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
        foreach ($this->languages as $lang => $langName) {
            $this->checkObject($data, 'settings_' . $lang);
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

        // Load base configuration
        $configObj = $this->load((object)array());
        $configObj = json_decode(json_encode($configObj['load']));
        $database = $this->db->databases[$configObj->database];

        // Copy data into new Object
        $newConfigObj = new stdClass();
        $newConfigObj->version = 1;
        foreach ($this->languages as $lang => $langName) {
            if (property_exists($data->{'settings_' . $lang}, 'title')) {
                $newConfigObj->{'settings_' . $lang}->title = $data->{'settings_' . $lang}->title;
            } else {
                $newConfigObj->{'settings_' . $lang}->title = '';
            }
            if (property_exists($data->{'settings_' . $lang}, 'author')) {
                $newConfigObj->{'settings_' . $lang}->author = $data->{'settings_' . $lang}->author;
            } else {
                $newConfigObj->{'settings_' . $lang}->author = '';
            }
            if (property_exists($data->{'settings_' . $lang}, 'description')) {
                $newConfigObj->{'settings_' . $lang}->description = $data->{'settings_' . $lang}->description;
            } else {
                $newConfigObj->{'settings_' . $lang}->description = '';
            }
            if (property_exists($data->{'settings_' . $lang}, 'robots')) {
                $newConfigObj->{'settings_' . $lang}->robots = $data->{'settings_' . $lang}->robots;
            } else {
                $newConfigObj->{'settings_' . $lang}->robots = '';
            }
        }

        // Check existence of field configuration
        $query = $database->select(
            $this->prefix . 'seo_data',
            array(
                '*'
            ),
            array(
                'filePathName = \'' . $database->escape(ltrim($dir['relPath'] . '/' . $data->fileName, '/')) . '\''
            )
        );
        if ($query === false) {
            throw new Exception('Can not query table [' . $this->prefix . 'seo_data]');
        }
        $fileConfig = false;
        if ($row = $query->fetch()) {
            $fileConfig = $row['dataJson'];
        }

        // Write
        if ($fileConfig === false) {
            // Adding file configuration
            $query = $database->insert(
                $this->prefix . 'seo_data',
                array(
                    'filePathName' => ltrim($dir['relPath'] . '/' . $data->fileName, '/'),
                    'dataJson' => json_encode($newConfigObj)
                )
            );
            if ($query === false) {
                throw new Exception('Can not update table [' . $this->prefix . 'seo_data]');
            }
        } else {
            // Updating file configuration
            $query = $database->update(
                $this->prefix . 'seo_data',
                array(
                    'dataJson' => json_encode($newConfigObj)
                ),
                array(
                    'filePathName = \'' . ltrim($database->escape($dir['relPath'] . '/' . $data->fileName), '/') . '\''
                )
            );
            if ($query === false) {
                throw new Exception('Can not update table [' . $this->prefix . 'seo_data]');
            }
        }

        // Save data to database
        // $this->setFileConfig($dir['relPath'] . '/' . $data->fileName, $fileConfigObj);

        return array(
            'dataSave' => true,
            'fileData' => $newConfigObj,
            'fileConfig' => $fileConfig
        );
    }

}