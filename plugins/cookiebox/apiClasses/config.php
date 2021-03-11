<?php

require_once __DIR__ . '/../../../api/apiBaseClass.php';

class cookiebox_config extends apiBaseClass
{

    /**
     * Load configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function load($data)
    {
        // No permission check - Public function

        // No user input

        // Load config
        $configJson = file_get_contents(__DIR__ . '/../../../config/cookiebox_settings.json');
        $config = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);

        // Get version
        if (!property_exists($config, 'version')) {
            $config->version = 1;
        }

        // Get apperence
        if (!property_exists($config, 'apperence')) {
            $config->apperence = new stdClass();
        }

        if (!property_exists($config->apperence, 'box')) {
            $config->apperence->box = new stdClass();
            $config->apperence->box->detailsText = '';
            $config->apperence->box->text = '';
            $config->apperence->box->backgroundColor = '#ffffff';
            $config->apperence->box->fontColor = '#000000';
            $config->apperence->box->linkColor = '#0000ff';
        }

        if (!property_exists($config->apperence, 'preferedButton')) {
            $config->apperence->preferedButton = new stdClass();
            $config->apperence->preferedButton->text = '';
            $config->apperence->preferedButton->backgroundColor = '#000000';
            $config->apperence->preferedButton->textColor = '#ffffff';
        }

        if (!property_exists($config->apperence, 'button')) {
            $config->apperence->button = new stdClass();
            $config->apperence->button->text = '';
            $config->apperence->button->backgroundColor = '#f0f0f0';
            $config->apperence->button->textColor = '#909090';
        }

        if (!property_exists($config->apperence, 'detailsButton')) {
            $config->apperence->detailsButton = new stdClass();
            $config->apperence->detailsButton->text = '';
            $config->apperence->detailsButton->backgroundColor = '#000000';
            $config->apperence->detailsButton->textColor = '#ffffff';
        }

        if (!property_exists($config->apperence, 'settingsLink')) {
            $config->apperence->settingsLink = new stdClass();
            $config->apperence->settingsLink->text = '';
            $config->apperence->settingsLink->textColor = '#c0c0c0';
        }

        // Get cookies
        if (!property_exists($config, 'cookies')) {
            $config->cookies = new stdClass();
        } else {
            foreach (array_keys((array)$config->cookies) as $cookieName) {
                foreach ($this->languages as $language => $languageName) {
                    if (($language !== 'default') && (!property_exists($config->cookies->{$cookieName}->title, $language))) {
                        $config->cookies->{$cookieName}->title->{$language} = $data->cookies->{$cookieName}->title->{'default'};
                    }
                    if (($language !== 'default') && (!property_exists($config->cookies->{$cookieName}->info, $language))) {
                        $config->cookies->{$cookieName}->info->{$language} = $data->cookies->{$cookieName}->info->{'default'};
                    }
                    if (($language !== 'default') && (!property_exists($config->cookies->{$cookieName}->privacyURL, $language))) {
                        $config->cookies->{$cookieName}->privacyURL->{$language} = $data->cookies->{$cookieName}->privacyURL->{'default'};
                    }
                }
            }
        }

        return array(
            'load' => $config
        );
    }

    /**
     * Save configuration
     * @param string $data Parameters
     * @return array with result
     */
    public function save($data)
    {
        // Check permission
        if ($this->tools->checkRole('admin') === false) {
            throw new Exception('Permission denied');
        }

        // Check user input
        $this->checkObject($data, 'apperence');
        $this->checkObject($data, 'cookies');

        // Load current configuration
        $configJson = file_get_contents(__DIR__ . '/../../../config/cookiebox_settings.json');
        $config = json_decode($configJson);

        // Update version
        if (!property_exists($config, 'version')) {
            $config->version = 1;
        }

        // Update apperence
        if (!property_exists($config, 'apperence')) {
            $config->apperence = new stdClass();
        }

        if (!property_exists($config->apperence, 'box')) {
            $config->apperence->box = new stdClass();
        }
        $config->apperence->box->text = $data->apperence->box->text;
        $config->apperence->box->detailsText = $data->apperence->box->detailsText;
        $config->apperence->box->backgroundColor = $data->apperence->box->backgroundColor;
        $config->apperence->box->fontColor = $data->apperence->box->fontColor;
        $config->apperence->box->linkColor = $data->apperence->box->linkColor;

        if (!property_exists($config->apperence, 'preferedButton')) {
            $config->apperence->preferedButton = new stdClass();
        }
        $config->apperence->preferedButton->text = $data->apperence->preferedButton->text;
        $config->apperence->preferedButton->backgroundColor = $data->apperence->preferedButton->backgroundColor;
        $config->apperence->preferedButton->textColor = $data->apperence->preferedButton->textColor;

        if (!property_exists($config->apperence, 'button')) {
            $config->apperence->button = new stdClass();
        }
        $config->apperence->button->text = $data->apperence->button->text;
        $config->apperence->button->backgroundColor = $data->apperence->button->backgroundColor;
        $config->apperence->button->textColor = $data->apperence->button->textColor;

        if (!property_exists($config->apperence, 'detailsButton')) {
            $config->apperence->detailsButton = new stdClass();
        }
        $config->apperence->detailsButton->text = $data->apperence->detailsButton->text;
        $config->apperence->detailsButton->backgroundColor = $data->apperence->detailsButton->backgroundColor;
        $config->apperence->detailsButton->textColor = $data->apperence->detailsButton->textColor;

        if (!property_exists($config->apperence, 'settingsLink')) {
            $config->apperence->settingsLink = new stdClass();
        }
        $config->apperence->settingsLink->text = $data->apperence->settingsLink->text;
        $config->apperence->settingsLink->textColor = $data->apperence->settingsLink->textColor;

        // Update cookies
        $config->cookies = new stdClass(); // Always init to ensure removal of removed cookies
        foreach (array_keys((array)$data->cookies) as $cookieName) {
            $cookieName = preg_replace('/[^a-zA-Z]/', '', $cookieName);
            $config->cookies->{$cookieName} = (object)array(
                'required' => $data->cookies->{$cookieName}->required
            );
            $config->cookies->{$cookieName}->info = new stdClass();
            $config->cookies->{$cookieName}->privacyURL = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->cookies->{$cookieName}->title->{$language} = $data->cookies->{$cookieName}->title->{$language};
                $config->cookies->{$cookieName}->info->{$language} = $data->cookies->{$cookieName}->info->{$language};
                $config->cookies->{$cookieName}->privacyURL->{$language} = $data->cookies->{$cookieName}->privacyURL->{$language};
            }
        }

        // Save configuration
        file_put_contents(__DIR__ . '/../../../config/cookiebox_settings.json', json_encode($config, JSON_PRETTY_PRINT));

        return array(
            'save' => true
        );
    }

}