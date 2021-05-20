<?php

// Called from: ../../inc/init.php

$beyond->plugins->captcha = new stdClass();

// Includes
include_once __DIR__ . '/inc/class_captchaHandler.php';

$beyond->captcha = new captchaHandler(
    $_SESSION[$beyond->prefix . 'data']['language'],
    $beyond->prefix,
    $beyond->tools,
    $beyond->config
);

// Initialize session variables
if (!array_key_exists('plugin_captcha', $_SESSION[$beyond->prefix . 'data'])) {
    $_SESSION[$beyond->prefix . 'data']['plugin_captcha'] = array(
    );
}
