<?php

header('Content-type: text/html; Charset=UTF-8');
require_once __DIR__ . '/../../inc/init.php';

?>

// <script>

    /*
     * Add IFrame to body
     */

    function <?php print $prefix; ?>cookieboxInit() {
        beyond_api.cookiebox_config.load({}, function (error, data) {
            if (error !== false) {
                alert('Error loading cookiebox: ' + error);
                return;
            }

            document.body.innerHTML =
                '<div class="<?php print $prefix; ?>cookieboxWrap">' +
                '<iframe class="<?php print $prefix; ?>cookieboxFrame" id="<?php print $prefix; ?>cookiebox" src="<?php print $config->get('base', 'server.baseUrl') . '/beyond/plugins/cookiebox/cookieboxFrame.php'; ?>"></iframe>' +
                '</div>' +
                document.body.innerHTML;

        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        <?php print $prefix; ?>cookieboxInit();
    });

