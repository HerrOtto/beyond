<?php

require_once __DIR__ . '/../../../api/apiBaseClass.php';
require_once __DIR__ . '/../inc/class_captcha.php';

class captcha_base extends beyondApiBaseClass
{

    /**
     * Get captcha image and secret as base64 encoded image
     * @param string $data Parameters
     * @return array with result
     */
    public function init($data)
    {
        // Public function no auth reuired

        // No user input

        //
        $result = false;

        $configJson = file_get_contents(__DIR__ . '/../../../config/captcha_settings.json');
        $config = json_decode($configJson);

        $captchaClass = new captcha();
        $captcha = $captchaClass->CreateImage(
            $config->apperence->width,
            $config->apperence->height,
            $config->security->minLength,
            $config->security->maxLength,
            $config->security->maxRotation
        );

        if ($captcha !== false) {

            $result = $captcha;
            $id = uniqid('', true);

            if (! array_key_exists('captcha', $_SESSION[$this->prefix . 'data'])) {
                $_SESSION[$this->prefix . 'data']['captcha'] = array();
            }
            $_SESSION[$this->prefix . 'data']['captcha'][$id] = $captcha['text'];

            $result = array(
                'pngBase64' => 'data:image/png;base64,' . base64_encode($captcha['image']),
                'id' => $id
            );

        }

        return array(
            'init' => $result
        );
    }

    /**
     * Check if user input matches captcha letters
     * @param string $data Parameters
     * @return array with result
     */
    public function check($data)
    {
        // Public function no auth reuired

        // Check user input
        $this->checkString($data, 'id', true, false);
        $this->checkString($data, 'text', true, false);

        //
        if (! array_key_exists('captcha', $_SESSION[$this->prefix . 'data'])) {
            return array(
                'check' => false
            );
        }

        if (! array_key_exists($data->id, $_SESSION[$this->prefix . 'data']['captcha'])) {
            return array(
                'check' => false
            );
        }

        if ($_SESSION[$this->prefix . 'data']['captcha'][$data->id] !== $data->text) {
            // Remove used captcha
            unset($_SESSION[$this->prefix . 'data']['captcha'][$data->id]);
            return array(
                'check' => false
            );
        }

        // Remove used captcha
        unset($_SESSION[$this->prefix . 'data']['captcha'][$data->id]);
        return array(
            'check' => true
        );
    }

}