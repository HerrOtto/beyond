<?php

header("Content-type: text/javascript; Charset=UTF-8");
require_once __DIR__ . '/../../inc/init.php';

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

        if (<?php print $beyond->prefix; ?>cookieboxGetCookie('cookieboxDone') !== '1') {

            // Add cookiebox
            beyond_api.cookiebox_config.load({}, function (error, data) {
                if (error !== false) {
                    alert('Error loading cookiebox: ' + error);
                    return;
                }

                // Cookiebox
                document.body.innerHTML =
                    '<div class="<?php print $beyond->prefix; ?>cookieboxWrap">' +
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

    document.addEventListener("DOMContentLoaded", function () {
        <?php print $beyond->prefix; ?>cookieboxInit();
    });

