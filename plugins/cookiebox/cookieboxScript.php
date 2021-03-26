<?php

header("Content-type: text/javascript; Charset=UTF-8");
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

// <script>

    /*
     * Check cookie
     */

    function <?php print $beyond->prefix; ?>cookieboxGetCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    /*
     * Add IFrame to body
     */

    function <?php print $beyond->prefix; ?>cookieboxInit() {
        if (<?php print $beyond->prefix; ?>cookieboxGetCookie('cookieboxDone') !== '<?php print $configObj->changeCount; ?>') {

            // Add cookiebox
            beyond_api.cookiebox_config.load({}, function (error, data) {
                if (error !== false) {
                    alert('Error loading cookiebox: ' + error);
                    return;
                }

                // Cookiebox
                document.body.innerHTML =
                    '<div class="<?php print $beyond->prefix; ?>cookieboxWrap" id="<?php print $beyond->prefix; ?>cookieboxWrap" style="width: 600px; height: 400px;">' +
                    '<iframe class="<?php print $beyond->prefix; ?>cookieboxFrame" id="<?php print $beyond->prefix; ?>cookiebox" src="<?php print $beyond->config->get('base', 'server.baseUrl') . '/beyond/plugins/cookiebox/cookieboxFrame.php'; ?>"></iframe>' +
                    '</div>' +
                    document.body.innerHTML;

                // Background
                document.body.innerHTML =
                    '<div class="<?php print $beyond->prefix; ?>cookieboxBackground">' +
                    '</div>' + document.body.innerHTML;
            });
        }

    }

    function <?php print $beyond->prefix; ?>IframeEventHandler(event) {
        try {
            console.log(event);
            data = JSON.parse(event.data);
            if (data.kind === 'desiredHeight') {

                // Default height
                var height = 600;
                const maxHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)

                // Max 80% Window height
                if (parseInt(data.value) > maxHeight*0.8) {
                    height = maxHeight*0.8;
                } else {
                    height = parseInt(data.value);
                }

            height = Math.ceil(height);
                // Resize box
                var boxWrap = document.getElementById('<?php print $beyond->prefix; ?>cookieboxWrap');
                boxWrap.style.height = height + 'px';
            }
        } catch (e) {
            // Ignore
        }
    }

    if (window.addEventListener) {
        window.addEventListener("message", <?php print $beyond->prefix; ?>IframeEventHandler, false);
    } else {
        window.attachEvent("onmessage", <?php print $beyond->prefix; ?>IframeEventHandler);
    }

    document.addEventListener("DOMContentLoaded", function () {
        <?php print $beyond->prefix; ?>cookieboxInit();
    });

