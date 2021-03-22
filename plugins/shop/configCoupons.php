<?php

// Called from: config.php

?>
<script>

    var coupons = {};

    function createInputFields(prefix) {

        var fields = '';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_code">Code</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_code" type="text" />\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_value">Value</label>\n' +
            '  <input class="form-control py-4" id="' + prefix + '_value" type="text" />\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="' + prefix + '_kind">Kind</label>\n' +
            '  <select class="form-control mb-4" id="' + prefix + '_kind">\n' +
            '    <option value="percent" selected>percent</option>\n' +
            '    <option value="amount">amount</option>\n' +
            '  </option>\n' +
            '</div>';

        fields +=
            '<div class="form-group">\n' +
            '  <input id="' + prefix + '_disabled" type="checkbox"> Disabled\n' +
            '</div>';

        return fields;

    }

    function loadCoupons() {
        $('#coupons').empty();

        <?php print $beyond->prefix; ?>api.shop_coupons.fetch({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.fetch === 'object') && (data.fetch !== null)) {
                    coupons = data.fetch;

                    for (couponId in coupons) {

                        var out = '';

                        out += '<div class="shopItem">';
                        out += '<span class="shopItemIcon">';
                        out += '<i class="fas fa-box-open"></i>';
                        out += '</span>';
                        out += '<span class="shopItemName" onclick="editCoupon(\'' + couponId + '\', false);">';
                        out += coupons[couponId].code
                        out += '</span>';
                        out += '<span class="shopItemAction text-nowrap">';
                        out += '  <i class="fas fa-trash ml-1" onclick="deleteCoupon(\'' + couponId + '\', false);"></i>';
                        out += '</span>';
                        out += '</div>'

                        $('#coupons').append(out);
                    }

                } else {
                    message('Load coupons failed');
                }
            }
        });
    }

    function addCoupon(fromModal = false) {
        if (fromModal === false) {
            var fields = createInputFields('addCoupon');
            $('#dialogAddCoupon form').html(fields);
            $('#dialogAddCoupon').modal('show').on('shown.bs.modal', function (e) {
                $('#addCoupon_articleNo').focus();
            });
            return false;
        }

        var data = {
            'code': $('#addCoupon_code').val(),
            'value': $('#addCoupon_value').val(),
            'kind': $('#addCoupon_kind').val(),
            'disabled': $('#addCoupon_disabled').prop('checked') ? true : false
        };

        <?php print $beyond->prefix; ?>api.shop_coupons.add(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.add === true) {
                        $('#dialogAddCoupon').modal('hide');
                        loadCoupons();
                    } else {
                        message('Adding coupon failed');
                    }
                }
            });

    }

    function deleteCoupon(couponId, fromModal = false) {
        if (fromModal === false) {
            $('#dialogDeleteCoupon .modal-body').html('<div class="mb-4">Delete coupon from database</div>');
            $('#dialogDeleteCoupon').data('id', couponId).modal('show');
            return false;
        }

        var data = {
            'id': couponId,
        };

        <?php print $beyond->prefix; ?>api.shop_coupons.delete(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.delete === true) {
                        $('#dialogDeleteCoupon').modal('hide');
                        loadCoupons();
                    } else {
                        message('Coupon deletion failed');
                    }
                }
            });
    }

    function editCoupon(couponId) {
        var fields = createInputFields('editCoupon');
        $('#dialogEditCoupon form').html(fields);

        $('#editCoupon_code').val(coupons[couponId].code);
        $('#editCoupon_value').val(coupons[couponId].value);
        $('#editCoupon_kind').val(coupons[couponId].kind === 'percent' ? 'percent' : 'amount').change();
        if (coupons[couponId].disabled) {
            $('#editCoupon_disabled').prop('checked', true);
        } else {
            $('#editCoupon_disabled').removeProp('checked');
        }

        $('#dialogEditCoupon').data('couponId', couponId).modal('show').on('shown.bs.modal', function (e) {
            $('#editCoupon_articleNo').focus();
        });
    }

    function saveCoupon(couponId) {

        var data = {
            'id': couponId,
            'code': $('#editCoupon_code').val(),
            'value': $('#editCoupon_value').val(),
            'kind': $('#editCoupon_kind').val(),
            'disabled': ($('#editCoupon_disabled').prop('checked') ? true : false)
        };

        <?php print $beyond->prefix; ?>api.shop_coupons.save(
            data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.save === true) {
                        $('#dialogEditCoupon').modal('hide');
                        loadCoupons();
                    } else {
                        message('Save coupon failed');
                    }
                }
            });

    }

    //

    $(document).ready(function () {
        loadCoupons();
    });
</script>

<!-- Add coupon -->
<div class="modal fade" id="dialogAddCoupon" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="addCoupon(true);">
                    Add coupon
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit coupon -->
<div class="modal fade" id="dialogEditCoupon" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="saveCoupon($('#dialogEditCoupon').data('couponId'));">
                    Save coupon
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete coupon -->
<div class="modal fade" id="dialogDeleteCoupon" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Delete coupon: ...
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="deleteCoupon($('#dialogDeleteCoupon').data('id'), true);">
                    Delete coupon
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

        <button class="btn btn-secondary" type="button" onclick="addCoupon();">Add coupon</button>

    </div>
</div>
<div style="clear: both;"></div>

<div id="coupons">

</div>