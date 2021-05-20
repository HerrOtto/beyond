<?php

// Called from: config.php

?>
<script>

    var countries = {};
    var shipping = {};
    var currentCountryId = false;
    var currentCountryCode = false;

    function load() {
        $('#countries').empty();
        $('#shipping').empty();

        <?php print $beyond->prefix; ?>api.shop_shipping.fetch({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.fetch === 'object') && (data.fetch !== null)) {
                    currentCountryCode = false;
                    countries = data.fetch;
                    for (countryId in countries) {
                        if (currentCountryId = countryId) {
                            currentCountryCode = countries[countryId].code;
                        }
                        var out = '';
                        out += '<div class="shopItem">';
                        out += '<span class="shopItemIcon">';
                        out += '<i class="fas fa-globe"></i>';
                        out += '</span>';
                        out += '<span class="shopItemName" onclick="editCountry(\'' + countryId + '\', false);">';
                        out += countries[countryId].code + ': ' + (
                            countries[countryId].value[beyond_language] == '' ?
                                countries[countryId].value['default'] :
                                countries[countryId].value[beyond_language]
                        );
                        out += '</span>';
                        out += '<span class="shopItemAction text-nowrap">';
                        out += '  <i class="fas fa-trash ml-1" onclick="deleteCountry(\'' + countryId + '\', false);"></i>';
                        out += '</span>';
                        out += '</div>'
                        $('#countries').append(out);
                    }
                    shipping = data.shipping;
                    for (shippingIndex in shipping[currentCountryCode]) {
                        var out = '';
                        out += '<div class="shopItem">';
                        out += '<span class="shopItemIcon">';
                        out += '<i class="fas fa-coins"></i>';
                        out += '</span>';
                        out += '<span class="shopItemName" onclick="editShipping(\'' + shipping[currentCountryCode][shippingIndex].id + '\', false);">';
                        out += 'Weight <= ' + shipping[currentCountryCode][shippingIndex].weight + 'g';
                        out += '</span>';
                        out += '<span class="shopItemAction text-nowrap">';
                        out += '  <i class="fas fa-trash ml-1" onclick="deleteShipping(\'' + shipping[currentCountryCode][shippingIndex].id + '\', false);"></i>';
                        out += '</span>';
                        out += '</div>'
                        $('#shipping').append(out);
                    }
                } else {
                    message('Load countries failed');
                }
            }
        });
    }

    //

    function createCountryInputFields(prefix) {

        var fields = '';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_code">Code</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_code" ' + (prefix === 'editCountry' ? 'readonly' : '') + ' type="text" />\n' +
            '</div>';

        for (lang in beyond_languages) {
            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="' + prefix + '_value">Country name [' + beyond_languages[lang] + ']</label>\n' +
                '  <input class="form-control py-4" id="' + prefix + '_value_' + lang + '" type="text" />\n' +
                '</div>';
        }

        return fields;

    }

    function addCountry(fromModal = false) {
        if (fromModal === false) {
            var fields = createCountryInputFields('addCountry');
            $('#dialogAddCountry form').html(fields);
            $('#dialogAddCountry').modal('show').on('shown.bs.modal', function (e) {
                $('#addCountry_code').focus();
            });
            return false;
        }

        var value = {};
        for (lang in beyond_languages) {
            value[lang] = $('#addCountry_value_' + lang).val()
        }

        var data = {
            'code': $('#addCountry_code').val(),
            'value': value
        };

        <?php print $beyond->prefix; ?>api.shop_shipping.addCountry(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.addCountry === true) {
                        $('#dialogAddCountry').modal('hide');
                        load();
                    } else {
                        message('Adding country failed');
                    }
                }
            });

    }

    function deleteCountry(countryId, fromModal = false) {
        if (fromModal === false) {
            $('#dialogDeleteCountry .modal-body').html('<div class="mb-4">Delete country from database</div>');
            $('#dialogDeleteCountry').data('id', countryId).modal('show');
            return false;
        }

        var data = {
            'id': countryId,
        };

        <?php print $beyond->prefix; ?>api.shop_shipping.deleteCountry(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.deleteCountry === true) {
                        $('#dialogDeleteCountry').modal('hide');
                        load();
                    } else {
                        message('Country deletion failed');
                    }
                }
            });
    }

    function editCountry(countryId) {

        currentCountryId = countryId;

        $('#countryList').hide();
        $('#countryItem').show();

        var fields = createCountryInputFields('editCountry');
        $('#editCountryForm').html(fields);
        $('#editCountryForm').data('countryId', countryId);

        $('#editCountry_code').val(countries[countryId].code);
        for (lang in beyond_languages) {
            $('#editCountry_value_' + lang).val(countries[countryId].value[lang]);
        }
    }

    function saveCountry(countryId) {

        var value = {};
        for (lang in beyond_languages) {
            value[lang] = $('#editCountry_value_' + lang).val()
        }

        var data = {
            'id': countryId,
            'code': $('#editCountry_code').val(),
            'value': value
        };

        <?php print $beyond->prefix; ?>api.shop_shipping.saveCountry(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.saveCountry === true) {
                        //
                    } else {
                        message('Save country failed');
                    }
                }
            });

    }

    function createShippingInputFields(prefix) {

        var fields = '';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_weight">Weight [g]</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_weight" ' + (prefix === 'editCountry' ? 'readonly' : '') + ' type="text" />\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_value">Price</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_value" ' + (prefix === 'editCountry' ? 'readonly' : '') + ' type="text" />\n' +
            '</div>';

        return fields;

    }

    function addShipping(fromModal = false) {
        if (fromModal === false) {
            var fields = createShippingInputFields('addShipping');
            $('#dialogAddShipping form').html(fields);
            $('#dialogAddShipping').modal('show').on('shown.bs.modal', function (e) {
                $('#addShipping_weight').focus();
            });
            return false;
        }

        var data = {
            'countryCode': countries[currentCountryId].code,
            'weight': $('#addShipping_weight').val(),
            'value': $('#addShipping_value').val()
        };

        <?php print $beyond->prefix; ?>api.shop_shipping.addShipping(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.addShipping === true) {
                        $('#dialogAddShipping').modal('hide');
                        load();
                    } else {
                        message('Adding cost failed');
                    }
                }
            });

    }

    function deleteShipping(shippingId, fromModal = false) {
        if (fromModal === false) {
            $('#dialogDeleteShipping .modal-body').html('<div class="mb-4">Delete cost from database</div>');
            $('#dialogDeleteShipping').data('id', shippingId).modal('show');
            return false;
        }

        var data = {
            'id': shippingId,
        };

        <?php print $beyond->prefix; ?>api.shop_shipping.deleteShipping(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.deleteShipping === true) {
                        $('#dialogDeleteShipping').modal('hide');
                        load();
                    } else {
                        message('Cost deletion failed');
                    }
                }
            });
    }

    function editShipping(shippingId, fromModal) {

        if (fromModal === false) {
            var fields = createShippingInputFields('editShipping');
            $('#dialogEditShipping form').html(fields);
            for (shippingIndex in shipping[currentCountryCode]) {
                if (shipping[currentCountryCode][shippingIndex].id == shippingId) {
                    $('#editShipping_weight').val(shipping[currentCountryCode][shippingIndex].weight);
                    $('#editShipping_value').val(shipping[currentCountryCode][shippingIndex].value);
                }
            }
            $('#dialogEditShipping').data('id', shippingId).modal('show').on('shown.bs.modal', function (e) {
                $('#addShipping_weight').focus();
            });
            return false;
        }

        var data = {
            'id': shippingId,
            'weight': $('#editShipping_weight').val(),
            'value': $('#editShipping_value').val()
        };

        <?php print $beyond->prefix; ?>api.shop_shipping.saveShipping(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.saveShipping === true) {
                        $('#dialogEditShipping').modal('hide');
                        load();
                    } else {
                        message('Save cost failed');
                    }
                }
            });

    }

    //

    $(document).ready(function () {
        load();
    });
</script>

<!-- Add country -->
<div class="modal fade" id="dialogAddCountry" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="addCountry(true);">
                    Add country
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete country -->
<div class="modal fade" id="dialogDeleteCountry" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Delete country: ...
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="deleteCountry($('#dialogDeleteCountry').data('id'), true);">
                    Delete country
                </button>
                <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Add shipping -->
<div class="modal fade" id="dialogAddShipping" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="addShipping(true);">
                    Add cost
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit shipping -->
<div class="modal fade" id="dialogEditShipping" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button"
                        onclick="editShipping($('#dialogEditShipping').data('id'), true);">
                    Save cost
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete shipping -->
<div class="modal fade" id="dialogDeleteShipping" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Delete cost: ...
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="deleteShipping($('#dialogDeleteShipping').data('id'), true);">
                    Delete cost
                </button>
                <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- country -->

<div id="countryList">
    <div style="width: 100%;">
        <div class="mb-4 float-left">

            <?php print $shopMenu; ?>

        </div>
        <div class="mb-4 float-right">

            <button class="btn btn-secondary" type="button" onclick="addCountry();">Add country</button>

        </div>
    </div>
    <div style="clear: both;"></div>
    <div id="countries" class="mb-4"></div>
</div>

<!-- costs -->

<div id="countryItem" style="display:none;">

    <div style="width: 100%;">
        <div class="mb-4 float-left">

            <button class="btn btn-secondary" type="button"
                    onclick="$('#countryItem').hide(); $('#countryList').show();">Back to country list
            </button>

        </div>
        <div class="mb-4 float-right">

            <button class="btn btn-secondary" type="button" onclick="addShipping();">Add cost</button>


        </div>
    </div>
    <div style="clear: both;"></div>

    <!-- Edit country -->
    <p>
        Country
    </p>
    <div class="card p-4">
        <form onsubmit="return false;" id="editCountryForm">
        </form>
        <button class="btn btn-success" type="button" onclick="saveCountry($('#editCountryForm').data('countryId'));">
            Save country
        </button>
    </div>

    <p class="pt-4">
        Costs
    </p>
    <div class="card p-4 mb-4">
        <div id="shipping"></div>
    </div>

</div>