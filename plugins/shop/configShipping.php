<?php

// Called from: config.php

?>
<script>

    var countries = {};

    function createCountryInputFields(prefix) {

        var fields = '';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_code">Code</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_code" type="text" />\n' +
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

    function loadCountries() {
        $('#countries').empty();
        $('#countryList').hide();
        $('#countryItem').hide();

        <?php print $beyond->prefix; ?>api.shop_shipping.fetch({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.fetch === 'object') && (data.fetch !== null)) {

                    countries = data.fetch;

                    for (countryId in countries) {
                        var out = '';

                        out += '<div class="shopItem">';
                        out += '<span class="shopItemIcon">';
                        out += '<i class="fas fa-globe"></i>';
                        out += '</span>';
                        out += '<span class="shopItemName" onclick="editCountry(\'' + countryId + '\', false);">';
                        out += countries[countryId].code
                        out += '</span>';
                        out += '<span class="shopItemAction text-nowrap">';
                        out += '  <i class="fas fa-trash ml-1" onclick="deleteCountry(\'' + countryId + '\', false);"></i>';
                        out += '</span>';
                        out += '</div>'

                        $('#countries').append(out);
                    }

                    $('#countryList').show();

                } else {
                    message('Load countries failed');
                }
            }
        });
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
            value[lang] = $('#addCountry_value_'+lang).val()
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
                        loadCountries();
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
                        loadCountries();
                    } else {
                        message('Country deletion failed');
                    }
                }
            });
    }

    function editCountry(countryId) {

        $('#countryList').hide();
        $('#countryItem').show();

        var fields = createCountryInputFields('editCountry');
        $('#editCountryForm').html(fields);
        $('#editCountryForm').data('countryId', countryId);

        $('#editCountry_code').val(countries[countryId].code);
        for (lang in beyond_languages) {
            $('#editCountry_value_'+lang).val(countries[countryId].value[lang]);
        }

        $('#editCountry_code').focus();
    }

    function saveCountry(countryId) {

        var value = {};
        for (lang in beyond_languages) {
            value[lang] = $('#editCountry_value_'+lang).val()
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

    //

    $(document).ready(function () {
        loadCountries();
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
    <div id="countries"></div>
</div>

<!-- costs -->

<div id="countryItem" style="display:none;">

    <div style="width: 100%;">
        <div class="mb-4 float-left">

            <button class="btn btn-secondary" type="button" onclick="loadCountries();">Back to country list</button>

        </div>
        <div class="mb-4 float-right">

            <button class="btn btn-secondary" type="button" onclick="addCountry();">Add cost</button>

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
    <div class="card p-4">
        <div id="costs"></div>
    </div>

</div>