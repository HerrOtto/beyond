<?php

// Called from: config.php

?>
<script>

    var items = {};

    function createInputFields(prefix) {

        var fields = '';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_articleNo">Article No.</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_articleNo" type="text" />\n' +
            '</div>';

        for (language in <?php print $beyond->prefix; ?>languages) {

            var langTitle = <?php print $beyond->prefix; ?>languages[language];

            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="' + prefix + '_name_' + language + '">Title/Name [' + langTitle + ']</label>\n' +
                '  <input class="form-control py-4" id="' + prefix + '_name_' + language + '" type="text" />\n' +
                '</div>';

            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="' + prefix + '_description_' + language + '">Description [' + langTitle + ']</label>\n' +
                '  <textarea rows=4 class="form-control" id="' + prefix + '_description_' + language + '"></textarea>\n' +
                '</div>';

        }

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_price">Price</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_price" type="text" />\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_weightGramm">Weight [gramm]</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_weightGramm" type="text" />\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_vatPercent">VAT percent</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_vatPercent" type="text" />\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <input id="' + prefix + '_disabled" type="checkbox"> Disabled\n' +
            '</div>';

        return fields;

    }

    function loadItems() {
        $('#items').empty();

        <?php print $beyond->prefix; ?>api.shop_items.fetch({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.fetch === 'object') && (data.fetch !== null)) {
                    console.log(data);
                    items = data.fetch;

                    for (itemId in items) {

                        var out = '';

                        out += '<div class="shopItem">';
                        out += '<span class="shopItemIcon">';
                        out += '<i class="fas fa-box-open"></i>';
                        out += '</span>';
                        out += '<span class="shopItemName" onclick="editItem(\'' + itemId + '\', false);">';
                        out += items[itemId].articleNo + ': ' + items[itemId].name.default;
                        out += '</span>';
                        out += '<span class="shopItemAction text-nowrap">';
                        out += '  <i class="fas fa-trash ml-1" onclick="deleteItem(\'' + itemId + '\', false);"></i>';
                        out += '</span>';
                        out += '</div>'

                        $('#items').append(out);
                    }

                } else {
                    message('Load items failed');
                }
            }
        });
    }

    function addItem(fromModal = false) {
        if (fromModal === false) {
            var fields = createInputFields('addItem');
            $('#dialogAddItem form').html(fields);
            $('#dialogAddItem').modal('show').on('shown.bs.modal', function (e) {
                $('#addItem_articleNo').focus();
            });
            return false;
        }

        var data = {
            'articleNo': $('#addItem_articleNo').val(),
            'price': $('#addItem_price').val(),
            'weightGramm': $('#addItem_weightGramm').val(),
            'vatPercent': $('#addItem_vatPercent').val(),
            'disabled': $('#addItem_disabled').prop('checked') ? true : false
        };

        for (language in <?php print $beyond->prefix; ?>languages) {
            data['name_' + language] = $('#addItem_name_' + language).val()
            data['description_' + language] = $('#addItem_description_' + language).val()
        }

        <?php print $beyond->prefix; ?>api.shop_items.add(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.add === true) {
                        $('#dialogAddItem').modal('hide');
                        loadItems();
                    } else {
                        message('Adding item failed');
                    }
                }
            });

    }

    function deleteItem(itemId, fromModal = false) {
        if (fromModal === false) {
            $('#dialogDeleteItem .modal-body').html('<div class="mb-4">Delete item from database</div>');
            $('#dialogDeleteItem').data('id', itemId).modal('show');
            return false;
        }

        var data = {
            'id': itemId,
        };

        <?php print $beyond->prefix; ?>api.shop_items.delete(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.delete === true) {
                        $('#dialogDeleteItem').modal('hide');
                        loadItems();
                    } else {
                        message('Item deletion failed');
                    }
                }
            });
    }

    function editItem(itemId) {
        var fields = createInputFields('editItem');
        $('#dialogEditItem form').html(fields);

        $('#editItem_articleNo').val(items[itemId].articleNo);
        $('#editItem_price').val(items[itemId].price);
        $('#editItem_weightGramm').val(items[itemId].weightGramm);
        $('#editItem_vatPercent').val(items[itemId].vatPercent);
        if (items[itemId].disabled) {
            $('#editItem_disabled').prop('checked', true);
        } else {
            $('#editItem_disabled').removeProp('checked');
        }

        for (language in <?php print $beyond->prefix; ?>languages) {
            $('#editItem_name_' + language).val(items[itemId].name[language]);
            $('#editItem_description_' + language).val(items[itemId].description[language]);
        }

        $('#dialogEditItem').data('itemId', itemId).modal('show').on('shown.bs.modal', function (e) {
            $('#editItem_articleNo').focus();
        });
    }

    function saveItem(itemId) {

        var data = {
            'id': itemId,
            'articleNo': $('#editItem_articleNo').val(),
            'price': $('#editItem_price').val(),
            'weightGramm': $('#editItem_weightGramm').val(),
            'vatPercent': $('#editItem_vatPercent').val(),
            'disabled': ($('#editItem_disabled').prop('checked') ? true : false)
        };

        for (language in <?php print $beyond->prefix; ?>languages) {
            data['name_' + language] = $('#editItem_name_' + language).val()
            data['description_' + language] = $('#editItem_description_' + language).val()
        }

        <?php print $beyond->prefix; ?>api.shop_items.save(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.save === true) {
                        $('#dialogEditItem').modal('hide');
                        loadItems();
                    } else {
                        message('Save item failed');
                    }
                }
            });

    }

    //

    $(document).ready(function () {
        loadItems();
    });
</script>

<!-- Add item -->
<div class="modal fade" id="dialogAddItem" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="addItem(true);">
                    Add item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit item -->
<div class="modal fade" id="dialogEditItem" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="saveItem($('#dialogEditItem').data('itemId'));">
                    Save item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete item -->
<div class="modal fade" id="dialogDeleteItem" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Delete item: ...
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="deleteItem($('#dialogDeleteItem').data('id'), true);">
                    Delete item
                </button>
                <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div style="width: 100%;">
    <div class="mb-4 float-left">

        <?php print $shopMenu; ?>

    </div>
    <div class="mb-4 float-right">

        <button class="btn btn-secondary" type="button" onclick="addItem();">Add item</button>

    </div>
</div>
<div style="clear: both;"></div>

<div id="items">

</div>