<?php

// Called from: ../../editor.php

// Variables from calling script:
// $editFile
// $dir['relPath']

if (pathinfo($editFile, PATHINFO_EXTENSION) !== 'php') {
    throw new Exception('This plugin only supports PHP files');
}

?>
<script>
    pluginSaveHandler.push(function(fileName, path) {
        var data = {};

        $('[id^="contentFieldItem_"]').each(function () {
            var id = $(this)[0].id;
            var elements = id.split('_', 3);
            var field = elements[1];
            var lang = elements[2];
            if (!(field in data)) {
                data[field] = {};
            }
            if (!(lang in data[field])) {
                data[field][lang] = $('#' + id).val();
            }
        });

        <?php print $beyond->prefix; ?>api.content_config.dataSave({
            'fileName': fileName,
            'path': path,
            'content': data
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.dataSave === true) {
                    //
                } else {
                    message('Saving data to content plugin failed: ' + data.dataSave);
                }
            }
        });

    });
</script>