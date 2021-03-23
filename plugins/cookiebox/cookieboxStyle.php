<?php

/*
 * IFrame positioning
 */

header("Content-type: text/css; Charset=UTF-8");
require_once __DIR__ . '/../../inc/init.php';

?>

/* <style> */

    .<?php print $beyond->prefix; ?>cookieboxBackground {

        z-index:9998;
        background-color: #000000;
        opacity: 0.5;
        position:fixed;
        top:0;
        left:0;
        right:0;
        bottom:0;

    }

    /* Mobile */
    .<?php print $beyond->prefix; ?>cookieboxWrap {

        z-index: 9999;
        position: fixed;

        top: 40px;
        left: 40px;
        right: 40px;
        bottom: 40px;

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
            width: 600px;
            height: 400px;

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
