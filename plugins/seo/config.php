<?php

// Called from: ../../pluginConfig.php

?>
<script>

    function load() {
        <?php print $beyond->prefix; ?>api.seo_config.load({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.load === 'object') && (data.load !== null)) {
                    $('#seoDatabase').val(data.load.database).change();
                    $('#seoDatabase').removeAttr('readonly');

                    for (lang in beyond_languages) {

                        var out = '';

                        out += '<div class="card mb-4">';
                        out += '    <div class="card-header">';
                        out += '        <strong>Default values [' + beyond_languages[lang] + ']</strong>';
                        out += '    </div>';
                        out += '    <div class="card-body">';
                        out += '        <strong>Title</strong>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="seoMetaTitlePrefix_' + lang + '">Title prefix</label>';
                        out += '           <input class="form-control py-4" id="seoMetaTitlePrefix_' + lang + '" type="text"';
                        out += '                   placeholder="Enter title prefix" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="seoMetaTitleSuffix_' + lang + '">Title suffix</label>';
                        out += '            <input class="form-control py-4" id="seoMetaTitleSuffix_' + lang + '" type="text"';
                        out += '                   placeholder="Enter title suffix" value="" readonly/>';
                        out += '        </div>';
                        out += '        <strong>Meta</strong>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="seoMetaAuthor_' + lang + '">Author (default)</label>';
                        out += '            <input class="form-control py-4" id="seoMetaAuthor_' + lang + '" type="text"';
                        out += '                   placeholder="Enter default author here" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="seoMetaDescription_' + lang + '">Description (default)</label>';
                        out += '           <input class="form-control py-4" id="seoMetaDescription_' + lang + '" type="text"';
                        out += '                   placeholder="Enter default description here" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="seoMetaRobots_' + lang + '">Robots (default)</label>';
                        out += '            <input class="form-control py-4" id="seoMetaRobots_' + lang + '" type="text"';
                        out += '                   placeholder="Enter default robots value here" value="" readonly/>';
                        out += '       </div>';
                        out += '    </div>';
                        out += '</div>';

                        $('#defaults').append(out);

                        $('#seoMetaTitlePrefix_' + lang).val(data.load['defaults_' + lang].titlePrefix);
                        $('#seoMetaTitlePrefix_' + lang).removeAttr('readonly');
                        $('#seoMetaTitleSuffix_' + lang).val(data.load['defaults_' + lang].titleSuffix);
                        $('#seoMetaTitleSuffix_' + lang).removeAttr('readonly');
                        $('#seoMetaAuthor_' + lang).val(data.load['defaults_' + lang].author);
                        $('#seoMetaAuthor_' + lang).removeAttr('readonly');
                        $('#seoMetaDescription_' + lang).val(data.load['defaults_' + lang].description);
                        $('#seoMetaDescription_' + lang).removeAttr('readonly');
                        $('#seoMetaRobots_' + lang).val(data.load['defaults_' + lang].robots);
                        $('#seoMetaRobots_' + lang).removeAttr('readonly');

                    }

                } else {
                    message('Load configuration failed: ' + data.load);
                }
            }
        });
    }

    function save() {
        var data = {
            'database': $('#seoDatabase').val()
        };
        for (lang in beyond_languages) {
            data['defaults_' + lang] = {
                'titlePrefix': $('#seoMetaTitlePrefix_' + lang).val(),
                'titleSuffix': $('#seoMetaTitleSuffix_' + lang).val(),
                'author': $('#seoMetaAuthor_' + lang).val(),
                'description': $('#seoMetaDescription_' + lang).val(),
                'robots': $('#seoMetaRobots_' + lang).val()
            }
        }
        // Send
        <?php print $beyond->prefix; ?>api.seo_config.save(data,
            function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.save === true) {
                        load();
                    } else {
                        message('Save configuration failed: ' + data.save);
                    }
                }
            });
    }

    //

    $(document).ready(function () {
        load();
    });
</script>

<div style="width: 100%;">
    <div class="mb-4 float-left" id="captchaTest">


    </div>
    <div class="mb-4 float-right">

        <button class="btn btn-secondary" type="button" onclick="save();">Save config</button>

    </div>
</div>
<div style="clear: both;"></div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Configuration</strong>
    </div>
    <div class="card-body">

        <strong>Storage</strong>

        <div class="form-group">
            <label class="small mb-1" for="seoDatabase">Database</label>
            <select class="form-control" id="seoDatabase">
                <?php
                print '<option value="" selected disabled></option>';
                foreach ($beyond->config->get('database', 'items', array()) as $databaseName => $databaseConfig) {
                    print '<option value="' . $databaseName . '">' . $databaseName . '</option>';
                }
                ?>
            </select>
        </div>

    </div>
</div>

<div id="defaults">

</div>


