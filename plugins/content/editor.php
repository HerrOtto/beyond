<?php

// Called from: ../../editor.php

// Variables from calling script:
// $editFile
// $dir['relPath']

$configJson = file_get_contents(__DIR__ . '/../../config/content_settings.json');
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

    function contentRenderField(fieldName, fieldConfig) {
        var field = '';
        var languages = <?php print $beyond->prefix; ?>languages;

        field += '<div class="mb-2" id="contentField_' + fieldName + '">';
        field += '<strong>' + fieldName + ' <i class="fas fa-trash" onclick="contentRemoveField(\'' + fieldName + '\', false);" style="cursor:pointer;"></i></strong>';
        for (lang in languages) {

            field += '<div class="form-group mb-0">';
            if (fieldConfig.kind == 'string') {
                field += '<label class="small" for="contentFieldItem_' + fieldName + '_' + lang + '">' + languages[lang] + '</label>';
                field += '<input class="form-control py-4" id="contentFieldItem_' + fieldName + '_' + lang + '" type="text" />';
            } else if (fieldConfig.kind == 'longtext') {
                field += '<label class="small" for="contentFieldItem_' + fieldName + '_' + lang + '">' + languages[lang] + '</label>';
                field += '<textarea rows=4 class="form-control" id="contentFieldItem_' + fieldName + '_' + languages[lang] + '"></textarea>';
            } else if (fieldConfig.kind == 'html') {
                field += '<label class="small" for="contentFieldItem_' + fieldName + '_' + lang + '">' + languages[lang] + '</label>';
                field += '<textarea rows=4 class="form-control" id="contentFieldItem_' + fieldName + '_' + languages[lang] + '"></textarea>';
            }
            field += '</div>';
        }
        field += '</div>';

        return field;
    }

    function contentAddField(fromModal = false) {
        if (fromModal === false) {
            $('#contentFieldName').val('');
            $('#contentFieldKind').val('string').change();
            $('#contentDialogAddField').modal('show').on('shown.bs.modal', function (e) {
                $('#contentFieldName').focus();
            });
            return false;
        }
        // Send
        <?php print $beyond->prefix; ?>api.content_config.addField({
            'fileName': <?php print json_encode($editFile); ?>,
            'path': <?php print json_encode($dir['relPath']); ?>,
            'fieldName': $('#contentFieldName').val(),
            'fieldKind': $('#contentFieldKind').val()
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                $('#contentFields').append(
                    contentRenderField(data.addField, data.config.fields[data.addField])
                );
                $('#contentDialogAddField').modal('hide');
            }
        });
    }

    function contentLoadFields() {
        <?php print $beyond->prefix; ?>api.content_config.loadFields({
            'fileName': <?php print json_encode($editFile); ?>,
            'path': <?php print json_encode($dir['relPath']); ?>
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.loadFields === true) {
                    var languages = <?php print $beyond->prefix; ?>languages;
                    for (field in data.config.fields) {
                        $('#contentFields').append(
                            contentRenderField(field, data.config.fields[field], data.content)
                        );

                    }
                    for (field in data.content.fields) {
                        for (lang in languages) {
                            $('#contentFieldItem_' + field + '_' + lang).val(
                                lang in data.content.fields[field] ? data.content.fields[field][lang] : ''
                            );
                        }
                    }
                } else {
                    message('Loading content plugin fields failed: ' + data.loadFields);
                }
            }
        });
    }

    function contentRemoveField(fieldName, fromModal = false) {
        if (fromModal === false) {
            $('#contentDialogRemoveField .modal-body').html('Remove field: <b>' + fieldName + '</b> and all Data?');
            $('#contentDialogRemoveField').data('fieldName', fieldName).modal('show');
            return false;
        }
        // Send
        <?php print $beyond->prefix; ?>api.content_config.removeField({
            'fileName': <?php print json_encode($editFile); ?>,
            'path': <?php print json_encode($dir['relPath']); ?>,
            'fieldName': fieldName
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.removeField === true) {
                    $('#contentField_' + fieldName).remove();
                    $('#contentDialogRemoveField').modal('hide');
                } else {
                    message('Removing field to content plugin failed: ' + data.addField);
                }
            }
        });
    }

    $(function () {

        contentLoadFields();

    });

</script>

<!-- Add field -->
<div class="modal fade" id="contentDialogAddField" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">
                    <div class="form-group">
                        <label class="small mb-1" for="contentFieldName">Field name</label>
                        <input class="form-control py-4" id="contentFieldName" type="text"
                               placeholder="Enter new field name here"/>
                    </div>
                    <div class="form-group">
                        <label class="small mb-1" for="contentFieldKind">Field type</label>
                        <select class="form-control" id="contentFieldKind">
                            <option selected val="string">string</option>
                            <option val="longtext">longtext</option>
                            <option val="longtext">html</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button"
                        onclick="contentAddField(true);">
                    Add field
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove field -->
<div class="modal fade" id="contentDialogRemoveField" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Remove field: ...
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="contentRemoveField($('#contentDialogRemoveField').data('fieldName'), true);">
                    Remove field
                </button>
                <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div>
    <div id="contentFields">
    </div>
</div>

<button id="contentButtonAdd" class="btn btn-secondary" type="button" onclick="contentAddField();">Add content field
</button>