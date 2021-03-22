<?php

// Called from: ../../pluginSite.php

?>
<script>

    var blocks = {};

    function loadBlocks() {
        $('#blocks').empty();

        <?php print $beyond->prefix; ?>api.blocks_config.loadBlocks({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.loadBlocks === 'object') && (data.loadBlocks !== null)) {
                    blocks = data.loadBlocks;

                    for (blockName in blocks) {

                        var out = '';

                        out += '<div class="blockItem">';
                        out += '<span class="blockItemIcon">';
                        out += '<i class="fas fa-square"></i>';
                        out += '</span>';
                        out += '<span class="blockItemName" onclick="editBlock(\'' + btoa(blockName) + '\', false);">';
                        out += blockName;
                        out += '</span>';
                        out += '<span class="blockItemAction text-nowrap">';
                        out += '  <i class="fas fa-trash ml-1" onclick="deleteBlock(\'' + btoa(blockName) + '\', false);"></i>';
                        out += '</span>';
                        out += '</div>'

                        $('#blocks').append(out);
                    }

                } else {
                    message('Load blocks failed');
                }
            }
        });
    }

    function addBlock(fromModal = false) {
        if (fromModal === false) {

            var fields = '';

            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="addBlockName">Block name</label>\n' +
                '  <input class="form-control py-4" id="addBlockName" type="text" placeholder="Enter unique block name here"/>\n' +
                '</div>';

            fields +=
                '<div class="form-group mb-4">\n' +
                '  <label class="small mb-1" for="addBlockKind">Block kind</label>\n' +
                '  <select class="form-control" id="addBlockKind">\n' +
                '    <option value="text">text</option>\n' +
                '    <option value="html">html</option>\n' +
                '  </select>\n' +
                '</div>';

            for (language in <?php print $beyond->prefix; ?>languages) {
                fields +=
                    '<div class="mb-1">' +
                    '<strong>' + <?php print $beyond->prefix; ?>languages[language] + '</strong>' +
                    '</div>';
                fields +=
                    '<div class="form-group">\n' +
                    '  <label class="small mb-1" for="addBlockValue_' + language + '">Value</label>\n' +
                    '  <textarea rows=4 class="form-control" id="addBlockValue_' + language + '"></textarea>\n' +
                    '</div>';

            }

            $('#dialogAddBlock form').html(fields);
            $('#dialogAddBlock').modal('show').on('shown.bs.modal', function (e) {
                $('#addBlockName').focus();
            });
            return false;
        }

        var data = {
            'name': $('#addBlockName').val(),
            'kind': $('#addBlockKind').val()
        };
        for (language in <?php print $beyond->prefix; ?>languages) {
            data['value_' + language] = $('#addBlockValue_' + language).val()
        }

        <?php print $beyond->prefix; ?>api.blocks_config.addBlock(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.addBlock === true) {
                        $('#dialogAddBlock').modal('hide');
                        loadBlocks();
                    } else {
                        message('Adding block failed');
                    }
                }
            });

    }

    function deleteBlock(blockNameBase64 = '', fromModal = false) {
        if (fromModal === false) {
            $('#dialogDeleteBlock .modal-body').html('<div class="mb-4">Delete block <b>' + atob(blockNameBase64) + '</b> from database</div>');
            $('#dialogDeleteBlock').data('name', atob(blockNameBase64)).modal('show');
            return false;
        }

        var data = {
            'name': atob(blockNameBase64),
        };

        <?php print $beyond->prefix; ?>api.blocks_config.deleteBlock(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.deleteBlock === true) {
                        $('#dialogDeleteBlock').modal('hide');
                        loadBlocks();
                    } else {
                        message('Block deletion failed');
                    }
                }
            });

    }

    function editBlock(nameBase64) {

        var fields = '';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="editBlockName">Block name</label>\n' +
            '  <input class="form-control py-4" id="editBlockName" type="text" readonly />\n' +
            '</div>';

        for (language in <?php print $beyond->prefix; ?>languages) {
            fields +=
                '<div class="mb-1">' +
                '<strong>' + <?php print $beyond->prefix; ?>languages[language] + '</strong>' +
                '</div>';
            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="editBlockValue_' + language + '">Value</label>\n' +
                '  <textarea rows=4 class="form-control" id="editBlockValue_' + language + '"></textarea>\n' +
                '</div>';

        }

        $('#dialogEditBlock form').html(fields);
        $('#editBlockName').val(atob(nameBase64));
        for (language in <?php print $beyond->prefix; ?>languages) {
            if ((blocks[atob(nameBase64)].content !== null) && (language in blocks[atob(nameBase64)].content)) {
                $('#editBlockValue_' + language).val(blocks[atob(nameBase64)].content[language]);
            } else {
                $('#editBlockValue_' + language).val('');
            }
        }
        $('#dialogEditBlock').modal('show').on('shown.bs.modal', function (e) {
            $('#editBlockValue_default').focus();
        });

    }

    function saveBlock() {

        var data = {
            'name': $('#editBlockName').val(),
        };
        for (language in <?php print $beyond->prefix; ?>languages) {
            data['value_' + language] = $('#editBlockValue_' + language).val()
        }

        <?php print $beyond->prefix; ?>api.blocks_config.saveBlock(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.saveBlock === true) {
                        $('#dialogEditBlock').modal('hide');
                    } else {
                        message('Save block failed');
                    }
                }
            });

    }

    //

    $(document).ready(function () {
        loadBlocks();
    });
</script>

<!-- Add block -->
<div class="modal fade" id="dialogAddBlock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="addBlock(true);">
                    Add block
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit block -->
<div class="modal fade" id="dialogEditBlock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="saveBlock();">
                    Save block
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete block -->
<div class="modal fade" id="dialogDeleteBlock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Delete block: ...
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="deleteBlock(btoa($('#dialogDeleteBlock').data('name')), true);">
                    Delete block
                </button>
                <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div style="width: 100%;">
    <div class="mb-4 float-right">

        <button class="btn btn-secondary" type="button" onclick="addBlock();">Add block</button>

    </div>
</div>
<div style="clear: both;"></div>

<div id="blocks">

</div>