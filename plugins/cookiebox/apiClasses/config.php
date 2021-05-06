<?php

include_once __DIR__ . '/../../../api/apiBaseClass.php';

class cookiebox_config extends beyondApiBaseClass
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

        if (!property_exists($config, 'changeCount')) {
            $config->changeCount = 1;
        }

        if (!property_exists($config->apperence, 'box')) {
            $config->apperence->box = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->box->detailsText->{$language} = '';
                $config->apperence->box->text->{$language} = '';
            }
            $config->apperence->box->backgroundColor = '#ffffff';
            $config->apperence->box->fontColor = '#000000';
            $config->apperence->box->linkColor = '#0000ff';
            $config->apperence->box->css = '';
        }
        if (is_string($config->apperence->box->detailsText)) {
            $temp = $config->apperence->box->detailsText;
            $config->apperence->box->detailsText = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->box->detailsText->{$language} = $temp;
            }
        }
        if (is_string($config->apperence->box->text)) {
            $temp = $config->apperence->box->text;
            $config->apperence->box->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->box->text->{$language} = $temp;
            }
        }

        if (!property_exists($config->apperence, 'preferedButton')) {
            $config->apperence->preferedButton = new stdClass();
            $config->apperence->preferedButton->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->preferedButton->text->{$language} = '';
            }
            $config->apperence->preferedButton->backgroundColor = '#000000';
            $config->apperence->preferedButton->textColor = '#ffffff';
        }
        if (is_string($config->apperence->preferedButton->text)) {
            $temp = $config->apperence->preferedButton->text;
            $config->apperence->preferedButton->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->preferedButton->text->{$language} = $temp;
            }
        }

        if (!property_exists($config->apperence, 'button')) {
            $config->apperence->button = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->button->text->{$language} = '';
            }
            $config->apperence->button->backgroundColor = '#f0f0f0';
            $config->apperence->button->textColor = '#909090';
        }
        if (is_string($config->apperence->button->text)) {
            $temp = $config->apperence->button->text;
            $config->apperence->button->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->button->text->{$language} = $temp;
            }
        }

        if (!property_exists($config->apperence, 'detailsButton')) {
            $config->apperence->detailsButton = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->detailsButton->text->{$language} = '';
            }
            $config->apperence->detailsButton->backgroundColor = '#000000';
            $config->apperence->detailsButton->textColor = '#ffffff';
        }
        if (is_string($config->apperence->detailsButton->text)) {
            $temp = $config->apperence->detailsButton->text;
            $config->apperence->detailsButton->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->detailsButton->text->{$language} = $temp;
            }
        }

        if (!property_exists($config->apperence, 'settingsLink')) {
            $config->apperence->settingsLink = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->settingsLink->text->{$language} = '';
            }
            $config->apperence->settingsLink->textColor = '#c0c0c0';
        }
        if (is_string($config->apperence->settingsLink->text)) {
            $temp = $config->apperence->settingsLink->text;
            $config->apperence->settingsLink->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->settingsLink->text->{$language} = $temp;
            }
        }

        if (!property_exists($config->apperence, 'privacyLink')) {
            $config->apperence->privacyLink = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->privacyLink->text->{$language} = '';
            }
            $config->apperence->privacyLink->textColor = '#000000';
        }
        if (is_string($config->apperence->privacyLink->text)) {
            $temp = $config->apperence->privacyLink->text;
            $config->apperence->privacyLink->text = new stdClass();
            foreach ($this->languages as $language => $languageName) {
                $config->apperence->privacyLink->text->{$language} = $temp;
            }
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

        // Increment change counter
        if (!property_exists($config, 'changeCount')) {
            $config->changeCount = 0;
        }
        $config->changeCount += 1;

        // Update apperence
        if (!property_exists($config, 'apperence')) {
            $config->apperence = new stdClass();
        }

        if (!property_exists($config->apperence, 'box')) {
            $config->apperence->box = new stdClass();
        }
        if (!property_exists($config->apperence, 'text')) {
            $config->apperence->box->text = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->box->text->{$language} = $data->apperence->box->text->{$language};
        }
        if (!property_exists($config->apperence, 'detailsText')) {
            $config->apperence->box->detailsText = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->box->detailsText->{$language} = $data->apperence->box->detailsText->{$language};
        }
        $config->apperence->box->backgroundColor = $data->apperence->box->backgroundColor;
        $config->apperence->box->fontColor = $data->apperence->box->fontColor;
        $config->apperence->box->linkColor = $data->apperence->box->linkColor;
        $config->apperence->css = $data->apperence->css;

        if (!property_exists($config->apperence, 'preferedButton')) {
            $config->apperence->preferedButton = new stdClass();
        }
        if (!property_exists($config->apperence->preferedButton, 'text')) {
            $config->apperence->preferedButton->text = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->preferedButton->text->{$language} = $data->apperence->preferedButton->text->{$language};
        }
        $config->apperence->preferedButton->backgroundColor = $data->apperence->preferedButton->backgroundColor;
        $config->apperence->preferedButton->textColor = $data->apperence->preferedButton->textColor;

        if (!property_exists($config->apperence, 'button')) {
            $config->apperence->button = new stdClass();
        }
        if (!property_exists($config->apperence->button, 'text')) {
            $config->apperence->button->text = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->button->text->{$language} = $data->apperence->button->text->{$language};
        }
        $config->apperence->button->backgroundColor = $data->apperence->button->backgroundColor;
        $config->apperence->button->textColor = $data->apperence->button->textColor;

        if (!property_exists($config->apperence, 'detailsButton')) {
            $config->apperence->detailsButton = new stdClass();
        }
        if (!property_exists($config->apperence->detailsButton, 'text')) {
            $config->apperence->detailsButton->text = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->detailsButton->text->{$language} = $data->apperence->detailsButton->text->{$language};
        }
        $config->apperence->detailsButton->backgroundColor = $data->apperence->detailsButton->backgroundColor;
        $config->apperence->detailsButton->textColor = $data->apperence->detailsButton->textColor;

        if (!property_exists($config->apperence, 'settingsLink')) {
            $config->apperence->settingsLink = new stdClass();
        }
        if (!property_exists($config->apperence->settingsLink, 'text')) {
            $config->apperence->settingsLink->text = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->settingsLink->text->{$language} = $data->apperence->settingsLink->text->{$language};
        }
        $config->apperence->settingsLink->textColor = $data->apperence->settingsLink->textColor;

        if (!property_exists($config->apperence, 'privacyLink')) {
            $config->apperence->privacyLink = new stdClass();
        }
        if (!property_exists($config->apperence->privacyLink, 'text')) {
            $config->apperence->privacyLink->text = new stdClass();
        }
        foreach ($this->languages as $language => $languageName) {
            $config->apperence->privacyLink->text->{$language} = $data->apperence->privacyLink->text->{$language};
        }
        $config->apperence->privacyLink->textColor = $data->apperence->privacyLink->textColor;

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