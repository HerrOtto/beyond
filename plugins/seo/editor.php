<?php

// Called from: ../../editor.php

// Variables from calling script:
// $editFile
// $dir['relPath']

$configJson = file_get_contents(__DIR__ . '/../../config/seo_settings.json');
$configObj = json_decode($configJson);

if (!property_exists($configObj, 'version')) {
    $configObj->version = 1;
}

if (!property_exists($configObj, 'database')) {
    $configObj->database = $beyond->config->get('database', 'defaultDatabase');
}
$database = $beyond->db->databases[$configObj->database];

?>
<script>

    function seoRenderField(lang, fieldName, fileData, config) {
        var field = '';
        var languages = <?php print $beyond->prefix; ?>languages;

        field += '<div class="mb-2" id="seoFieldItem_' + fieldName + '">';
        field += '<strong>' + fieldName + '</strong>';

        field += '<div class="form-group mb-0">';
        field += '<label class="small" for="seoFieldItem_' + fieldName + '_' + lang + '">' + languages[lang] + '</label>';
        field += '<input class="form-control py-4" id="seoFieldItem_' + fieldName + '_' + lang + '" type="text" />';
        field += '</div>';
        field += '</div>';

        return field;
    }

    function seoLoadFields() {
        <?php print $beyond->prefix; ?>api.seo_config.loadFileData({
            'fileName': <?php print json_encode($editFile); ?>,
            'path': <?php print json_encode($dir['relPath']); ?>
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.loadFileData === true) {
                    var languages = <?php print $beyond->prefix; ?>languages;
                    // Default values: data.config
                    // Fields: data.fileData
                    for (lang in beyond_languages) {
                        $('#seoFields').append(
                            seoRenderField(lang, 'title', data.fileData, data.config)
                        );
                        $('#seoFieldItem_title' + '_' + lang).val(
                            ('settings_' + lang in data.fileData) && (data.fileData['settings_' + lang].title) ? data.fileData['settings_' + lang].title : ''
                        );
                    }
                    for (lang in beyond_languages) {
                        $('#seoFields').append(
                            seoRenderField(lang, 'author', data.fileData, data.config)
                        );
                        $('#seoFieldItem_author' + '_' + lang).val(
                            ('settings_' + lang in data.fileData) && (data.fileData['settings_' + lang].author) ? data.fileData['settings_' + lang].author : ''
                        );
                    }
                    for (lang in beyond_languages) {
                        $('#seoFields').append(
                            seoRenderField(lang, 'description', data.fileData, data.config)
                        );
                        $('#seoFieldItem_description' + '_' + lang).val(
                            ('settings_' + lang in data.fileData) && (data.fileData['settings_' + lang].description) ? data.fileData['settings_' + lang].description : ''
                        );
                    }
                    for (lang in beyond_languages) {
                        $('#seoFields').append(
                            seoRenderField(lang, 'robots', data.fileData, data.config)
                        );
                        $('#seoFieldItem_robots' + '_' + lang).val(
                            ('settings_' + lang in data.fileData) && (data.fileData['settings_' + lang].robots) ? data.fileData['settings_' + lang].robots : ''
                        );
                    }
                } else {
                    message('Loading seo plugin fields failed: ' + data.loadFields);
                }
            }
        });
    }

    $(function () {

        seoLoadFields();

    });

</script>

<div>
    <div id="seoFields">
    </div>
</div>

