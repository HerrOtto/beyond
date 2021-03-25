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
if (!property_exists($configObj, 'changeCount')) {
    $configObj->changeCount = 1;
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
        .<?php print $beyond->prefix; ?>cookieboxIntro {
        }

        /* Into bottom */
        .<?php print $beyond->prefix; ?>cookieboxIntroButtons {
        }

        /* Into text */
        .<?php print $beyond->prefix; ?>cookieboxIntro {
            margin-bottom: 20px;
            font-weight: lighter;
            font-size: 16pt;
            overflow-x: hidden;
            overflow-y: auto;

            text-align: justify;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .<?php print $beyond->prefix; ?>cookieboxIntro a {
            font-weight: lighter;
            font-size: 16pt;
        }

        /* Button */
        .<?php print $beyond->prefix; ?>cookieboxButton {
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
        .<?php print $beyond->prefix; ?>cookieboxPreferedButton {
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
        .<?php print $beyond->prefix; ?>cookieboxSettingsIntro {
            margin-bottom: 20px;
            text-align: justify;
            font-weight: lighter;
            font-size: 16pt;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .<?php print $beyond->prefix; ?>cookieboxSettingsIntro a {
            font-weight: lighter;
            font-size: 16pt;
        }

        /* Settings text */
        .<?php print $beyond->prefix; ?>cookieboxSettings {
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
        .<?php print $beyond->prefix; ?>cookieBoxDetails {
            font-size: 12pt;
        }

        .<?php print $beyond->prefix; ?>cookieBoxDetails strong {
            font-size: 16pt;
            padding-top: 7px;
        }

        .<?php print $beyond->prefix; ?>cookieBoxLink {
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

        function <?php print $beyond->prefix; ?>cookieboxSetCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + ";<?php
                $domain = $beyond->config->get('base', 'site.session.domain', '');
                if ($domain !== '') {
                    print 'domain=' . $beyond->config->get('base', 'site.session.domain', '') . ';';
                }
                ?>path=/";
        }

        /*
         * Intro on resize
         */

        function <?php print $beyond->prefix; ?>cookieboxScaleIntro() {
            setTimeout(<?php print $beyond->prefix; ?>cookieboxScaleIntro, 1000);

            buttonsDiv = document.getElementById('<?php print $beyond->prefix; ?>cookieboxIntroButtons')
            buttonsOffsetHeight = buttonsDiv.offsetHeight;

            buttonsDiv.style.position = 'absolute';
            buttonsDiv.style.display = 'block';
            buttonsDiv.style.bottom = '10';
            buttonsDiv.style.right = '0';
            buttonsDiv.style.left = '0';

            textDiv = document.getElementById('<?php print $beyond->prefix; ?>cookieboxIntro')

            textDiv.style.position = 'absolute';
            textDiv.style.display = 'block';
            textDiv.style.padding = '20px';
            textDiv.style.top = '0';
            textDiv.style.bottom = (buttonsDiv.offsetHeight + 30) + 'px';
            textDiv.style.marginBottom = '0';
            textDiv.style.right = '0';
            textDiv.style.left = '0';

        }

        document.addEventListener('DOMContentLoaded', function () {
            <?php print $beyond->prefix; ?>cookieboxScaleIntro();
        }, false);

        /*
         * Intro -> Settings
         */

        function <?php print $beyond->prefix; ?>cookieboxOpenSettings() {
            document.getElementById("<?php print $beyond->prefix; ?>cookieBoxStartpage").style.display = "none";
            document.getElementById("<?php print $beyond->prefix; ?>cookieBoxSettings").style.display = "block";
        }

        function <?php print $beyond->prefix; ?>cookieboxReloadPage() {
            var location = parent.location.href;
            if (location.indexOf("#") > 0) {
                location = location.split('#')[0]
            }
            if (location.indexOf("nocache") > 0) {
                window.parent.location.href = location;
            } else {
                window.parent.location.href = location + (location.indexOf("?") > 0 ? '&' : '?') + 'nocache=' + (new Date().getTime()).toString();
            }
        }

        function <?php print $beyond->prefix; ?>cookieboxAcceptSelected() {
            <?php
            foreach (array_keys((array)$configObj->cookies) as $cookieName) {
                print 'var checked = document.getElementById("' . $beyond->prefix . 'cookieBoxCheckbox_' . $cookieName . '").checked;' . PHP_EOL;
                print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_' . $cookieName . '\', checked === true ? \'1\' : \'0\', 30);' . PHP_EOL;
            }
            print $beyond->prefix . 'cookieboxSetCookie(\'cookieboxDone\', \'<?php print $configObj->changeCount; ?>\', 30);' . PHP_EOL;
            print $beyond->prefix . 'cookieboxReloadPage();' . PHP_EOL;
            ?>
        }

        function <?php print $beyond->prefix; ?>cookieboxAcceptMinimal() {
            <?php
            foreach (array_keys((array)$configObj->cookies) as $cookieName) {
                if ($configObj->cookies->{$cookieName}->required) {
                    //print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_\', value, 30)' . PHP_EOL;
                    print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_' . $cookieName . '\', \'1\', 30);' . PHP_EOL;
                } else {
                    print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_' . $cookieName . '\', \'0\', 30);' . PHP_EOL;
                }
            }
            print $beyond->prefix . 'cookieboxSetCookie(\'cookieboxDone\', \'<?php print $configObj->changeCount; ?>\', 30);' . PHP_EOL;
            print $beyond->prefix . 'cookieboxReloadPage();' . PHP_EOL;
            ?>
        }

        function <?php print $beyond->prefix; ?>cookieboxAcceptAll() {
            <?php
            foreach (array_keys((array)$configObj->cookies) as $cookieName) {
                print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_' . $cookieName . '\', \'1\', 30);' . PHP_EOL;
            }
            print $beyond->prefix . 'cookieboxSetCookie(\'cookieboxDone\', \'<?php print $configObj->changeCount; ?>\', 30);' . PHP_EOL;
            print $beyond->prefix . 'cookieboxReloadPage();' . PHP_EOL;
            ?>
        }
    </script>

</head>
<body>

<!-- Startpage -->
<div align="center" id="<?php print $beyond->prefix; ?>cookieBoxStartpage">
    <div class="<?php print $beyond->prefix; ?>cookieboxIntro" id="<?php print $beyond->prefix; ?>cookieboxIntro">
        <?php print $configObj->apperence->box->text; ?>
    </div>
    <div align="center" class="<?php print $beyond->prefix; ?>cookieboxIntroButtons"
         id="<?php print $beyond->prefix; ?>cookieboxIntroButtons">
        <span class="<?php print $beyond->prefix; ?>cookieboxPreferedButton"
              onclick="<?php print $beyond->prefix; ?>cookieboxAcceptAll();"><?php print $configObj->apperence->preferedButton->text; ?></span>
        <span class="<?php print $beyond->prefix; ?>cookieboxButton"
              onclick="<?php print $beyond->prefix; ?>cookieboxAcceptMinimal();"><?php print $configObj->apperence->button->text; ?></span>
        <span class="<?php print $beyond->prefix; ?>cookieboxSettings"
              onclick="<?php print $beyond->prefix; ?>cookieboxOpenSettings();"><?php print $configObj->apperence->settingsLink->text; ?></span>
    </div>
</div>

<!-- Settings -->
<div align="center" id="<?php print $beyond->prefix; ?>cookieBoxSettings" style="display:none;">
    <div align="left" class="<?php print $beyond->prefix; ?>cookieboxSettingsIntro">
        <?php print $configObj->apperence->box->detailsText; ?>
    </div>
    <div id="<?php print $beyond->prefix; ?>cookieBoxItems" align="left">

        <?php
        foreach (array_keys((array)$configObj->cookies) as $cookieName) {

            // -- Wrap --
            print '<div class="' . $beyond->prefix . 'cookieboxItemWrap" style="margin-bottom: 20px;">';

            // -- Left --
            print '<div style="width:60px; float:left; margin-right: -60px;">';

            // Checkbox
            print '<input class="apple-switch" type="checkbox" id="' . $beyond->prefix . 'cookieBoxCheckbox_' . $cookieName . '" ' . ($configObj->cookies->{$cookieName}->required ? 'checked disabled' : '') . '>';


            // --- Right --
            print '</div>';
            print '<div style="margin-left: 60px;">';

            // Details & Link
            print '<div class="' . $beyond->prefix . 'cookieBoxDetails" align="justify">';
            print '<strong style="display:block;">' . $configObj->cookies->{$cookieName}->title->{$_SESSION[$beyond->prefix . 'data']['language']} . '</strong>';
            print $configObj->cookies->{$cookieName}->info->{$_SESSION[$beyond->prefix . 'data']['language']};
            if ($configObj->cookies->{$cookieName}->privacyURL->{$_SESSION[$beyond->prefix . 'data']['language']} !== '') {
                print '<a style="display:block;" target="_blank" href="' . $configObj->cookies->{$cookieName}->privacyURL->{$_SESSION[$beyond->prefix . 'data']['language']} . '" class="' . $beyond->prefix . 'cookieBoxLink">';
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
        <span class="<?php print $beyond->prefix; ?>cookieboxPreferedButton"
              onclick="<?php print $beyond->prefix; ?>cookieboxAcceptSelected();"><?php print $configObj->apperence->detailsButton->text; ?></span>
    </div>
</div>

</body>
</html>