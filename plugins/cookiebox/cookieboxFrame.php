<?php

header('Content-type: text/html; Charset=UTF-8');
require_once __DIR__ . '/../../inc/init.php';

$displayLanguage = $_SESSION[$beyond->prefix . 'data']['language'];
if ($beyond->variable->get('lang', '') !== '') {
    $displayLanguage = $beyond->variable->get('lang');
}

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
    $configObj->apperence->box->backgroundColor = '#ffffff';
    $configObj->apperence->box->fontColor = '#000000';
    $configObj->apperence->box->linkColor = '#0000ff';
}
if (!property_exists($configObj->apperence->box, 'text')) {
    $configObj->apperence->box->text = new stdClass();
}
if (!property_exists($configObj->apperence->box, 'detailsText')) {
    $configObj->apperence->box->detailsText = new stdClass();
}
if (is_string($configObj->apperence->box->detailsText)) {
    $temp = $configObj->apperence->box->detailsText;
    $configObj->apperence->box->detailsText = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->apperence->box->detailsText->{$language} = $temp;
    }
}
if (is_string($configObj->apperence->box->text)) {
    $temp = $configObj->apperence->box->text;
    $configObj->apperence->box->text = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->apperence->box->text->{$language} = $temp;
    }
}

if (!property_exists($configObj->apperence, 'preferedButton')) {
    $configObj->apperence->preferedButton = new stdClass();
    $configObj->apperence->preferedButton->backgroundColor = '#000000';
    $configObj->apperence->preferedButton->textColor = '#ffffff';
}
if (!property_exists($configObj->apperence->preferedButton, 'text')) {
    $configObj->apperence->preferedButton->text = new stdClass();
}
if (is_string($configObj->apperence->preferedButton->text)) {
    $temp = $configObj->apperence->preferedButton->text;
    $configObj->apperence->preferedButton->text = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->apperence->preferedButton->text->{$language} = $temp;
    }
}

if (!property_exists($configObj->apperence, 'button')) {
    $configObj->apperence->button = new stdClass();
    $configObj->apperence->button->text = '';
    $configObj->apperence->button->backgroundColor = '#f0f0f0';
    $configObj->apperence->button->textColor = '#909090';
}
if (!property_exists($configObj->apperence->button, 'text')) {
    $configObj->apperence->button->text = new stdClass();
}
if (is_string($configObj->apperence->button->text)) {
    $temp = $configObj->apperence->button->text;
    $configObj->apperence->button->text = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->apperence->button->text->{$language} = $temp;
    }
}

if (!property_exists($configObj->apperence, 'detailsButton')) {
    $configObj->apperence->detailsButton = new stdClass();
    $configObj->apperence->detailsButton->backgroundColor = '#000000';
    $configObj->apperence->detailsButton->textColor = '#ffffff';
}
if (!property_exists($configObj->apperence->detailsButton, 'text')) {
    $configObj->apperence->detailsButton->text = new stdClass();
}
if (is_string($configObj->detailsButton->button->text)) {
    $temp = $configObj->detailsButton->button->text;
    $configObj->detailsButton->button->text = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->detailsButton->button->text->{$language} = $temp;
    }
}

if (!property_exists($configObj->apperence, 'settingsLink')) {
    $configObj->apperence->settingsLink = new stdClass();
    $configObj->apperence->settingsLink->textColor = '#c0c0c0';
}
if (!property_exists($configObj->apperence->settingsLink, 'text')) {
    $configObj->apperence->settingsLink->text = new stdClass();
}
if (is_string($configObj->apperence->settingsLink->text)) {
    $temp = $configObj->apperence->settingsLink->text;
    $configObj->apperence->settingsLink->text = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->apperence->settingsLink->text->{$language} = $temp;
    }
}

if (!property_exists($configObj->apperence, 'privacyLink')) {
    $configObj->apperence->privacyLink = new stdClass();
    $configObj->apperence->privacyLink->textColor = '#000000';
}
if (!property_exists($configObj->apperence->privacyLink, 'text')) {
    $configObj->apperence->privacyLink->text = new stdClass();
}
if (is_string($configObj->privacyLink->button->text)) {
    $temp = $configObj->privacyLink->button->text;
    $configObj->privacyLink->button->text = new stdClass();
    foreach ($beyond->languages as $language => $languageName) {
        $configObj->privacyLink->button->text->{$language} = $temp;
    }
}

if (!property_exists($configObj, 'cookies')) {
    $configObj->cookies = new stdClass();
}

?>
<html>
<head>

    <style>

        /* - CUSTOM CSS --------------------------------------------------------------------------------------------- */

        <?php
                if (property_exists($configObj->apperence, 'css')) {
                    print $configObj->apperence->css;
                }
        ?>

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
         * Cookie
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
         * On resize
         */

        function <?php print $beyond->prefix; ?>cookieboxScaleIntro() {

            if (document.getElementById("<?php print $beyond->prefix; ?>cookieBoxStartpage").style.display !== 'none') {
                // Currently on start page

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

                contentDiv = document.getElementById('<?php print $beyond->prefix; ?>cookieboxIntroText')

                parent.postMessage(JSON.stringify({
                    'kind': 'desiredHeight',
                    'value': contentDiv.clientHeight + buttonsDiv.offsetHeight + 90
                }), "*");
            } else {
                // Currently on details page
                settingsDiv = document.getElementById('<?php print $beyond->prefix; ?>cookieBoxSettings');
                parent.postMessage(JSON.stringify({
                    'kind': 'desiredHeight',
                    'value': settingsDiv.clientHeight + 90
                }), "*");
            }

            // Recalculate every second
            setTimeout(<?php print $beyond->prefix; ?>cookieboxScaleIntro, 1000);
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

        /*
         * On button click set cookies
         */

        function <?php print $beyond->prefix; ?>cookieboxAcceptSelected() {
            <?php
            foreach (array_keys((array)$configObj->cookies) as $cookieName) {
                print 'var checked = document.getElementById("' . $beyond->prefix . 'cookieBoxCheckbox_' . $cookieName . '").checked;' . PHP_EOL;
                print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_' . $cookieName . '\', checked === true ? \'1\' : \'0\', 30);' . PHP_EOL;
            }
            print $beyond->prefix . 'cookieboxSetCookie(\'cookieboxDone\', \'' . $configObj->changeCount . '\', 30);' . PHP_EOL;
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
            print $beyond->prefix . 'cookieboxSetCookie(\'cookieboxDone\', \'' . $configObj->changeCount . '\', 30);' . PHP_EOL;
            print $beyond->prefix . 'cookieboxReloadPage();' . PHP_EOL;
            ?>
        }

        function <?php print $beyond->prefix; ?>cookieboxAcceptAll() {
            <?php
            foreach (array_keys((array)$configObj->cookies) as $cookieName) {
                print $beyond->prefix . 'cookieboxSetCookie(\'cookiebox_' . $cookieName . '\', \'1\', 30);' . PHP_EOL;
            }
            print $beyond->prefix . 'cookieboxSetCookie(\'cookieboxDone\', \'' . $configObj->changeCount . '\', 30);' . PHP_EOL;
            print $beyond->prefix . 'cookieboxReloadPage();' . PHP_EOL;
            ?>
        }
    </script>

</head>
<body>

<!-- Startpage -->
<div align="center" id="<?php print $beyond->prefix; ?>cookieBoxStartpage">
    <div class="<?php print $beyond->prefix; ?>cookieboxIntro" id="<?php print $beyond->prefix; ?>cookieboxIntro">
        <div id="<?php print $beyond->prefix; ?>cookieboxIntroText">
            <?php
            if ((property_exists($configObj->apperence->box->text, $displayLanguage)) && (trim($configObj->apperence->box->text->{$displayLanguage}) !== '')) {
                print $configObj->apperence->box->text->{$displayLanguage};
            } else {
                print $configObj->apperence->box->text->default;
            }
            ?>
        </div>
    </div>
    <div align="center" class="<?php print $beyond->prefix; ?>cookieboxIntroButtons"
         id="<?php print $beyond->prefix; ?>cookieboxIntroButtons">
        <span class="<?php print $beyond->prefix; ?>cookieboxPreferedButton"
              onclick="<?php print $beyond->prefix; ?>cookieboxAcceptAll();">
            <?php
            if ((property_exists($configObj->apperence->preferedButton->text, $displayLanguage)) && (trim($configObj->apperence->preferedButton->text->{$displayLanguage}) !== '')) {
                print $configObj->apperence->preferedButton->text->{$displayLanguage};
            } else {
                print $configObj->apperence->preferedButton->text->default;
            }
            ?>
        </span>
        <span class="<?php print $beyond->prefix; ?>cookieboxButton"
              onclick="<?php print $beyond->prefix; ?>cookieboxAcceptMinimal();">
            <?php
            if ((property_exists($configObj->apperence->button->text, $displayLanguage)) && (trim($configObj->apperence->button->text->{$displayLanguage}) !== '')) {
                print $configObj->apperence->button->text->{$displayLanguage};
            } else {
                print $configObj->apperence->button->text->default;
            }
            ?>
        </span>
        <span class="<?php print $beyond->prefix; ?>cookieboxSettings"
              onclick="<?php print $beyond->prefix; ?>cookieboxOpenSettings();">
            <?php
            if ((property_exists($configObj->apperence->settingsLink->text, $displayLanguage)) && (trim($configObj->apperence->settingsLink->text->{$displayLanguage}) !== '')) {
                print $configObj->apperence->settingsLink->text->{$displayLanguage};
            } else {
                print $configObj->apperence->settingsLink->text->default;
            }
            ?>
        </span>
    </div>
</div>

<!-- Settings -->
<div align="center" id="<?php print $beyond->prefix; ?>cookieBoxSettings" style="display:none;">
    <div align="left" class="<?php print $beyond->prefix; ?>cookieboxSettingsIntro">
        <?php
        if ((property_exists($configObj->apperence->box->detailsText, $displayLanguage)) && (trim($configObj->apperence->box->detailsText->{$displayLanguage}) !== '')) {
            print $configObj->apperence->box->detailsText->{$displayLanguage};
        } else {
            print $configObj->apperence->box->detailsText->default;
        }
        ?>
    </div>
    <div id="<?php print $beyond->prefix; ?>cookieBoxItems" align="left">

        <?php
        foreach (array_keys((array)$configObj->cookies) as $cookieName) {

            // -- Wrap --
            print '<div class="' . $beyond->prefix . 'cookieboxItemWrap" style="margin-bottom: 20px;">';

            // -- Left --
            print '<div style="width:60px; float:left; margin-right: -60px;">';

            // Checkbox
            if ($configObj->cookies->{$cookieName}->required) {
                print '<input class="apple-switch" type="checkbox" id="' . $beyond->prefix . 'cookieBoxCheckbox_' . $cookieName . '" checked disabled>';
            } else if ($_COOKIE['cookiebox_' . $cookieName] == 1) {
                print '<input class="apple-switch" type="checkbox" id="' . $beyond->prefix . 'cookieBoxCheckbox_' . $cookieName . '" checked>';
            } else {
                print '<input class="apple-switch" type="checkbox" id="' . $beyond->prefix . 'cookieBoxCheckbox_' . $cookieName . '">';
            }

            // --- Right --
            print '</div>';
            print '<div style="margin-left: 60px;">';

            // Details & Link
            print '<div class="' . $beyond->prefix . 'cookieBoxDetails" align="justify">';

            print '<strong style="display:block;">';
            if ((property_exists($configObj->cookies->{$cookieName}->title, $displayLanguage)) && (trim($configObj->cookies->{$cookieName}->title->{$displayLanguage}) !== '')) {
                print $configObj->cookies->{$cookieName}->title->{$displayLanguage};
            } else {
                print $configObj->cookies->{$cookieName}->title->default;
            }
            print '</strong>';

            if ((property_exists($configObj->cookies->{$cookieName}->info, $displayLanguage)) && (trim($configObj->cookies->{$cookieName}->info->{$displayLanguage}) !== '')) {
                print $configObj->cookies->{$cookieName}->info->{$displayLanguage};
            } else {
                print $configObj->cookies->{$cookieName}->info->default;
            }

            if (trim($configObj->cookies->{$cookieName}->privacyURL->{$displayLanguage}) !== '') {
                $link = $configObj->cookies->{$cookieName}->privacyURL->{$displayLanguage};
            } else if (trim($configObj->cookies->{$cookieName}->privacyURL->default) !== '') {
                $link = $configObj->cookies->{$cookieName}->privacyURL->default;
            } else {
                $link = "";
            }
            if ($link !== '') {
                print '<a style="display:block;" target="_blank" style="color:' . $configObj->apperence->privacyLink->textColor . ';" href="' . $link . '" class="' . $beyond->prefix . 'cookieBoxLink">';
                if ((property_exists($configObj->apperence->privacyLink->text, $displayLanguage)) && (trim($configObj->apperence->privacyLink->text->{$displayLanguage}) !== '')) {
                    print $configObj->apperence->privacyLink->text->{$displayLanguage};
                } else {
                    print $configObj->apperence->privacyLink->text->default;
                }
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
              onclick="<?php print $beyond->prefix; ?>cookieboxAcceptSelected();"><?php print $configObj->apperence->detailsButton->text->{$displayLanguage}; ?></span>
    </div>
</div>

</body>
</html>