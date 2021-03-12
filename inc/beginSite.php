<?php

/**
 * Incuded on all pages within site element
 */

// Include plugins
foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
    if (!is_dir($pluginDir)) {
        continue;
    }
    if (file_exists($pluginDir . '/beginSite.php')) {
        try {
            require_once $pluginDir . '/beginSite.php';
        } catch (Exception $e) {
            $beyond->exceptionHandler->add($e);
        }
    }
}
unset($pluginDir);

?>
<div id="alertContainer">

</div>
<script>

    function message(text) {
        var alertBox = $(
            '<div class="alert alert-danger mt-4" role="alert">' + // style="z-index: 1049;"
            text +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>'
        );
        $('#alertContainer').append(alertBox);

        alertBox.delay(2000).fadeOut().queue(function() { $(this).remove(); });
        $("html, body").animate({ scrollTop: 0 }, "slow");
    }

</script>