<?php

header('Content-type: text/html; Charset=UTF-8');
require_once __DIR__ . '/../../inc/init.php';

$configJson = file_get_contents(__DIR__ . '/../../config/cookiebox_settings.json');
$configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);

// Get version
if (!property_exists($configObj, 'version')) {
    $configObj->version = 1;
}

// Get apperence
if (!property_exists($configObj, 'apperence')) {
    $configObj->apperence = new stdClass();
}

if (!property_exists($configObj->apperence, 'box')) {
    $configObj->apperence->box = new stdClass();
    $configObj->apperence->box->text = '';
    $configObj->apperence->box->detailsText = '';
    $configObj->apperence->box->backgroundColor = '#ffffff';
    $configObj->apperence->box->fontColor = '#000000';
    $configObj->apperence->box->linkColor = '#0000ff';
}

if (!property_exists($configObj->apperence, 'preferedButton')) {
    $configObj->apperence->preferedButton = new stdClass();
    $configObj->apperence->preferedButton->text = '';
    $configObj->apperence->preferedButton->backgroundColor = '#000000';
    $configObj->apperence->preferedButton->textColor = '#ffffff';
}

if (!property_exists($configObj->apperence, 'button')) {
    $configObj->apperence->button = new stdClass();
    $configObj->apperence->button->text = '';
    $configObj->apperence->button->backgroundColor = '#f0f0f0';
    $configObj->apperence->button->textColor = '#909090';
}

if (!property_exists($configObj->apperence, 'detailsButton')) {
    $configObj->apperence->detailsButton = new stdClass();
    $configObj->apperence->detailsButton->text = '';
    $configObj->apperence->detailsButton->backgroundColor = '#000000';
    $configObj->apperence->detailsButton->textColor = '#ffffff';
}

if (!property_exists($configObj->apperence, 'settingsLink')) {
    $configObj->apperence->settingsLink = new stdClass();
    $configObj->apperence->settingsLink->text = '';
    $configObj->apperence->settingsLink->textColor = '#c0c0c0';
}

if (!property_exists($configObj, 'cookies')) {
    $configObj->cookies = new stdClass();
}

?>
<html>
<head>

    <style>

        /* ---------------------------------------------------------------------------------------------------------- */

        /* Into top */
        .<?php print $prefix; ?>cookieboxIntro {
        }

        /* Into bottom */
        .<?php print $prefix; ?>cookieboxIntroButtons {
        }

        /* Into text */
        .<?php print $prefix; ?>cookieboxIntro {
            margin-bottom: 20px;
            font-weight: lighter;
            font-size: 16pt;
            overflow-x: hidden;
            overflow-y: auto;

            text-align:justify;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .<?php print $prefix; ?>cookieboxIntro a {
            font-weight: lighter;
            font-size: 16pt;
        }

        /* Button */
        .<?php print $prefix; ?>cookieboxButton {
            cursor: pointer;
            background-color: <?php print $configObj->apperence->button->backgroundColor; ?>;;
            padding: 5px;
            margin-bottom: 5px;
            color: <?php print $configObj->apperence->button->textColor; ?>;
            display: block;
            text-align: center;
            font-size: 20pt;
            width: 90%;
        }

        /* Prefered button */
        .<?php print $prefix; ?>cookieboxPreferedButton {
            cursor: pointer;
            background-color: <?php print $configObj->apperence->preferedButton->backgroundColor; ?>;
            padding: 5px;
            margin-bottom: 5px;
            color: <?php print $configObj->apperence->preferedButton->textColor; ?>;
            display: block;
            text-align: center;
            font-size: 20pt;
            width: 90%;
        }


        /* ---------------------------------------------------------------------------------------------------------- */

        /* Settings into top */
        .<?php print $prefix; ?>cookieboxSettingsIntro {
            margin-bottom: 20px;
            text-align: justify;
            font-weight: lighter;
            font-size: 16pt;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .<?php print $prefix; ?>cookieboxSettingsIntro a {
            font-weight: lighter;
            font-size: 16pt;
        }

        /* Settings text */
        .<?php print $prefix; ?>cookieboxSettings {
            cursor: pointer;
            padding: 5px;
            margin-bottom: 5px;
            color: <?php print $configObj->apperence->settingsLink->textColor; ?>;
            display: block;
            text-align: center;
            font-size: 14pt;
            width: 90%;
        }

        /* Link to privacy policy of a cookie */
        .<?php print $prefix; ?>cookieBoxDetails {
            font-size: 12pt;
        }

        .<?php print $prefix; ?>cookieBoxDetails strong {
            font-size: 16pt;
            padding-top: 7px;
        }

        .<?php print $prefix; ?>cookieBoxLink {
            font-size: 10pt;
        }


        /* ---------------------------------------------------------------------------------------------------------- */

        /* Body */
        body {
            background-color: <?php print $configObj->apperence->box->backgroundColor; ?>;
            padding: 20px;
        }

        /* All elements */
        * {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16pt;
            color: <?php print $configObj->apperence->box->fontColor; ?>;
        }

        a {
            font-size: 10pt;
        }

        /* ---------------------------------------------------------------------------------------------------------- */

        /* Switch */
        input.apple-switch {
            position: relative;
            appearance: none;
            outline: none;
            width: 50px;
            height: 30px;
            background-color: #ffffff;
            border: 1px solid #D9DADC;
            border-radius: 50px;
            box-shadow: inset -20px 0 0 0 #ffffff;
            transition-duration: 200ms;
        }

        input.apple-switch:after {
            content: "";
            position: absolute;
            top: 1px;
            left: 1px;
            width: 26px;
            height: 26px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.2);
        }

        input.apple-switch:checked {
            border-color: #4ED164;
            box-shadow: inset 20px 0 0 0 #4ED164;
        }

        input.apple-switch:disabled {
            border-color: #C0C0C0 !important;
            box-shadow: inset 20px 0 0 0 #C0C0C0;
        }

        input.apple-switch:checked:after {
            left: 20px;
            box-shadow: -2px 4px 3px rgba(0, 0, 0, 0.05);
        }

        /* ---------------------------------------------------------------------------------------------------------- */

    </style>

    <script>

        /*
         * Cookie functions
         */

        function <?php print $prefix; ?>cookieboxSetCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function <?php print $prefix; ?>cookieboxGetCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function <?php print $prefix; ?>cookieboxEraseCookie(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }

        /*
         * Intro on resize
         */

        function <?php print $prefix; ?>cookieboxScaleIntro() {
            setTimeout(<?php print $prefix; ?>cookieboxScaleIntro, 1000);

            buttonsDiv = document.getElementById('<?php print $prefix; ?>cookieboxIntroButtons')
            buttonsOffsetHeight = buttonsDiv.offsetHeight;

            buttonsDiv.style.position = 'absolute';
            buttonsDiv.style.display = 'block';
            buttonsDiv.style.bottom = '10';
            buttonsDiv.style.right = '0';
            buttonsDiv.style.left = '0';

            textDiv = document.getElementById('<?php print $prefix; ?>cookieboxIntro')

            textDiv.style.position = 'absolute';
            textDiv.style.display = 'block';
            textDiv.style.padding = '20px';
            textDiv.style.top = '0';
            textDiv.style.bottom = (buttonsDiv.offsetHeight+30) + 'px';
            textDiv.style.marginBottom = '0';
            textDiv.style.right = '0';
            textDiv.style.left = '0';


            console.log('buttonsOffsetHeight: ' + buttonsOffsetHeight);
        }

        document.addEventListener('DOMContentLoaded', function () {
            <?php print $prefix; ?>cookieboxScaleIntro();
        }, false);

        /*
         * Intro -> Settings
         */

        function <?php print $prefix; ?>cookieboxOpenSettings() {
            document.getElementById("<?php print $prefix; ?>cookieBoxStartpage").style.display = "none";
            document.getElementById("<?php print $prefix; ?>cookieBoxSettings").style.display = "block";
        }

    </script>

</head>
<body>

<!-- Startpage -->
<div align="center" id="<?php print $prefix; ?>cookieBoxStartpage">
    <div class="<?php print $prefix; ?>cookieboxIntro" id="<?php print $prefix; ?>cookieboxIntro">
        <?php print $configObj->apperence->box->text; ?>
    </div>
    <div align="center" class="<?php print $prefix; ?>cookieboxIntroButtons"
         id="<?php print $prefix; ?>cookieboxIntroButtons">
        <span class="<?php print $prefix; ?>cookieboxPreferedButton"><?php print $configObj->apperence->preferedButton->text; ?></span>
        <span class="<?php print $prefix; ?>cookieboxButton"><?php print $configObj->apperence->button->text; ?></span>
        <span class="<?php print $prefix; ?>cookieboxSettings"
              onclick="<?php print $prefix; ?>cookieboxOpenSettings();"><?php print $configObj->apperence->settingsLink->text; ?></span>
    </div>
</div>

<!-- Settings -->
<div align="center" id="<?php print $prefix; ?>cookieBoxSettings" style="display:none;">
    <div align="left" class="<?php print $prefix; ?>cookieboxSettingsIntro">
        <?php print $configObj->apperence->box->detailsText; ?>
    </div>
    <div id="<?php print $prefix; ?>cookieBoxItems" align="left">

        <?php
        foreach (array_keys((array)$configObj->cookies) as $cookieName) {

            // -- Wrap --
            print '<div class="' . $prefix . 'cookieboxItemWrap" style="margin-bottom: 20px;">';

            // -- Left --
            print '<div style="width:60px; float:left; margin-right: -60px;">';

            // Checkbox
            print '<input class="apple-switch" type="checkbox" id="' . $prefix . 'cookieBoxCheckbox_' . $cookieName . '" ' . ($configObj->cookies->{$cookieName}->required ? 'checked disabled' : '') . '>';


            // --- Right --
            print '</div>';
            print '<div style="margin-left: 60px;">';

            // Details & Link
            print '<div class="' . $prefix . 'cookieBoxDetails" align="justify">';
            print '<strong style="display:block;">' . $configObj->cookies->{$cookieName}->title->{$_SESSION[$prefix . 'data']['language']} . '</strong>';
            print $configObj->cookies->{$cookieName}->info->{$_SESSION[$prefix . 'data']['language']};
            if ($configObj->cookies->{$cookieName}->privacyURL->{$_SESSION[$prefix . 'data']['language']} !== '') {
                print '<a style="display:block;" target="_blank" href="' . $configObj->cookies->{$cookieName}->privacyURL->{$_SESSION[$prefix . 'data']['language']} . '" class="' . $prefix . 'cookieBoxLink">';
                print '  Datenschutzbedingungen des Anbieters';
                print '</a>';
            }
            print '</div>';

            // --
            print '</div>';
            print '</div>';
            print '<div style="clear:both;"></div>';

        }
        ?>
    </div>
    <div align="center" style="margin-top:20px;">
        <span class="<?php print $prefix; ?>cookieboxPreferedButton"><?php print $configObj->apperence->detailsButton->text; ?></span>
    </div>
</div>

</body>
</html>