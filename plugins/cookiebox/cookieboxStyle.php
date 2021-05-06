<?php

/*
 * IFrame positioning
 */

header("Content-type: text/css; Charset=UTF-8");
require_once __DIR__ . '/../../inc/init.php';

$configJson = file_get_contents(__DIR__ . '/../../config/cookiebox_settings.json');
$configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);

// Get version
if (!property_exists($configObj, 'version')) {
    $configObj->version = 1;
}

// Get apperence
if (!property_exists($configObj, 'changeCount')) {
    $configObj->changeCount = 1;
}

?>
/* <style> */

    /* - CUSTOM CSS --------------------------------------------------------------------------------------------- */

    <?php
            if (property_exists($configObj->apperence, 'css')) {
                print $configObj->apperence->css;
            }
    ?>

    /* ---------------------------------------------------------------------------------------------------------- */

    .<?php print $beyond->prefix; ?>cookieboxBackground {

        z-index: 9998;
        background-color: #000000;
        opacity: 0.5;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;

    }

    /* Mobile */
    .<?php print $beyond->prefix; ?>cookieboxWrap {

        z-index: 9999;
        position: fixed;

        border: 1px solid silver;
        border-radius: 4px;
    }

    /* PC */
    @media only screen and (min-width: 767px) {
        .<?php print $beyond->prefix; ?>cookieboxWrap {
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    }

    /* Mobile */
    @media only screen and (max-width: 767px) {
        .<?php print $beyond->prefix; ?>cookieboxWrap {
            height: inherit !important;
            width: inherit !important;
            top: 40px;
            left: 40px;
            right: 40px;
            bottom: 40px;
        }
    }

    /* Fit IFrame into Wrapper */
    .<?php print $beyond->prefix; ?>cookieboxFrame {

        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        width: 100%;
        height: 100%;

        border: 0;

        border-radius: 4px;

    }
