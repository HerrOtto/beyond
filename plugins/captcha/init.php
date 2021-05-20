<?php

// Called from: ../../inc/init.php

$beyond->plugins->captcha = new captchaHandler(
    $_SESSION[$beyond->prefix . 'data']['language'],
    $beyond->prefix,
    $beyond->tools,
    $beyond->config
);
