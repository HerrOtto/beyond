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
    pluginSaveHandler.push(function (fileName, path) {
        var data = {
            'fileName': fileName,
            'path': path
        };
        for (lang in beyond_languages) {
            data['settings_'+lang] = {
                'title': $('#seoFieldItem_title_'+lang).val(),
                'author': $('#seoFieldItem_author_'+lang).val(),
                'description': $('#seoFieldItem_description_'+lang).val(),
                'robots': $('#seoFieldItem_robots_'+lang).val()
            }
        }

        <?php print $beyond->prefix; ?>api.seo_config.dataSave(data,
            function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.dataSave === true) {
                        //
                    } else {
                        message('Saving data to seo plugin failed: ' + data.dataSave);
                    }
                }
            });

    });
</script>