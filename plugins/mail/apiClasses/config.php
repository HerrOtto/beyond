<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';
include_once __DIR__ . '/../inc/class_mailDatabase.php';

class mail_config extends beyondApiBaseClass
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
        $configJson = file_get_contents(__DIR__ . '/../../../config/mail_settings.json');
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
            if (!property_exists($configObj, 'settings_' . $lang)) {
                $configObj->{'settings_' . $lang} = new stdClass();
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'subjectPrefix')) {
                $configObj->{'settings_' . $lang}->subjectPrefix = '';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'subjectSuffix')) {
                $configObj->{'settings_' . $lang}->subjectSuffix = ' | YOUR SITE';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'from')) {
                $configObj->{'settings_' . $lang}->from = '';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'to')) {
                $configObj->{'settings_' . $lang}->to = '';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'bcc')) {
                $configObj->{'settings_' . $lang}->bcc = '';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'to')) {
                $configObj->{'settings_' . $lang}->to = 'info@your-mail.here';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'footerText')) {
                $configObj->{'settings_' . $lang}->footerText = '';
            }

            if (!property_exists($configObj->{'settings_' . $lang}, 'footerHtml')) {
                $configObj->{'settings_' . $lang}->footerHtml = '';
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
            $this->checkObject($data, 'settings_' . $lang);
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
            if (!property_exists($data, 'settings_' . $lang)) {
                $configObj->{'settings_' . $lang} = new stdClass();
            }
            if (property_exists($data->{'settings_' . $lang}, 'subjectPrefix')) {
                $configObj->{'settings_' . $lang}->subjectPrefix = $data->{'settings_' . $lang}->subjectPrefix;
            }
            if (property_exists($data->{'settings_' . $lang}, 'subjectSuffix')) {
                $configObj->{'settings_' . $lang}->subjectSuffix = $data->{'settings_' . $lang}->subjectSuffix;
            }
            if (property_exists($data->{'settings_' . $lang}, 'from')) {
                $configObj->{'settings_' . $lang}->from = $data->{'settings_' . $lang}->from;
            }
            if (property_exists($data->{'settings_' . $lang}, 'replyTo')) {
                $configObj->{'settings_' . $lang}->to = $data->{'settings_' . $lang}->replyTo;
            }
            if (property_exists($data->{'settings_' . $lang}, 'bcc')) {
                $configObj->{'settings_' . $lang}->bcc = $data->{'settings_' . $lang}->bcc;
            }
            if (property_exists($data->{'settings_' . $lang}, 'to')) {
                $configObj->{'settings_' . $lang}->to = $data->{'settings_' . $lang}->to;
            }
            if (property_exists($data->{'settings_' . $lang}, 'footerText')) {
                $configObj->{'settings_' . $lang}->footerText = $data->{'settings_' . $lang}->footerText;
            }
            if (property_exists($data->{'settings_' . $lang}, 'footerHtml')) {
                $configObj->{'settings_' . $lang}->footerHtml = $data->{'settings_' . $lang}->footerHtml;
            }
        }

        // Database changed?
        if ($configObj->database !== $data->database) {

            // Current Database
            $databaseCurrent = $this->db->databases[$configObj->database];

            // Load data from current database
            $query = $databaseCurrent->select(
                $this->prefix . 'mail_data',
                array(
                    'id',
                    'date',
                    'from',
                    'to',
                    'replyTo',
                    'bcc',
                    'subject',
                    'mail',
                    'kind'
                ),
                array()
            );
            if ($query === false) {
                throw new Exception('Can not query table [' . $this->prefix . 'mail_data]');
            }
            $content = array();
            while ($row = $query->fetch()) {
                array_push($content, array(
                    'id' => $row['id'],
                    'date' => $row['date'],
                    'from' => $row['from'],
                    'to' => $row['to'],
                    'replyTo' => $row['replyTo'],
                    'bcc' => $row['bcc'],
                    'subject' => $row['subject'],
                    'mail' => $row['mail'],
                    'kind' => $row['kind']
                ));
            }

            $databaseNew = $this->db->databases[$data->database];
            $mailDatabase = new mailDatabase($this->prefix);
            $mailDatabase->init($databaseNew);

            // From now on: Rollback on failure
            try {

                // Write data to new database
                foreach ($content as $contentItem) {
                    $query = $databaseNew->insert(
                        $this->prefix . 'mail_data',
                        array(
                            'id' => $contentItem['id'],
                            'date' => $contentItem['date'],
                            'from' => $contentItem['from'],
                            'to' => $contentItem['to'],
                            'replyTo' => $contentItem['replyTo'],
                            'bcc' => $contentItem['bcc'],
                            'subject' => $contentItem['subject'],
                            'mail' => $contentItem['mail'],
                            'kind' => $contentItem['kind']
                        )
                    );
                    if ($query === false) {
                        throw new Exception('Can not insert into table [' . $this->prefix . 'mail_data]');
                    }
                }

            } catch (Exception $e) {

                // Rollback
                $mailDatabase->drop($databaseNew);

                // Replay exception
                throw new Exception($e->getMessage());

            }

            // Drop old database
            $mailDatabase->drop($databaseCurrent);

        }

        // Change database in configuration
        $configObj->database = $data->database;

        file_put_contents(__DIR__ . '/../../../config/mail_settings.json', json_encode($configObj, JSON_PRETTY_PRINT));

        return array(
            'save' => true,
            'config' => $configObj
        );
    }

}