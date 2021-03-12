<?php

// Called from: ../../pluginConfig.php

?>
<script>

    function load() {
        <?php print $beyond->prefix; ?>api.captcha_config.load({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.load === 'object') && (data.load !== null)) {
                    $('#captchaWidth').val(data.load.apperence.width);
                    $('#captchaWidth').removeAttr('readonly');
                    $('#captchaHeight').val(data.load.apperence.height);
                    $('#captchaHeight').removeAttr('readonly');
                    $('#captchaMinLength').val(data.load.security.minLength);
                    $('#captchaMinLength').removeAttr('readonly');
                    $('#captchaMaxLength').val(data.load.security.maxLength);
                    $('#captchaMaxLength').removeAttr('readonly');
                    $('#captchaMaxRotation').val(data.load.security.maxRotation);
                    $('#captchaMaxRotation').removeAttr('readonly');

                    getCaptcha();

                } else {
                    message('Load configuration failed: ' + data.load);
                }
            }
        });
    }

    function save() {
        <?php print $beyond->prefix; ?>api.captcha_config.save({
            'apperence': {
                'width': $('#captchaWidth').val(),
                'height': $('#captchaHeight').val()
            },
            'security': {
                'minLength': $('#captchaMinLength').val(),
                'maxLength': $('#captchaMaxLength').val(),
                'maxRotation': $('#captchaMaxRotation').val()
            }
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.save === true) {

                    getCaptcha();

                } else {
                    message('Save configuration failed: ' + data.save);
                }
            }
        });
    }

    //

    function getCaptcha() {
        <?php print $beyond->prefix; ?>api.captcha_base.init({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.init === 'object') && (data.init !== null)) {

                    $('#captchaTest').html(
                        'Test captcha: &nbsp;&nbsp; <span style="cursor: pointer" onclick="getCaptcha();"><i class="fas fa-sync"></i> Reload</span><br>' +
                        '<img src="' + data.init.pngBase64 + '" border=0 alt="Test captcha" style="border:1px solid silver;">'
                    );

                } else {
                    message('Get new captcha failed: ' + data.init);
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

        <button class="btn btn-success" type="button" onclick="save();">Save config</button>

    </div>
</div>
<div style="clear: both;"></div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Apperence</strong>
    </div>
    <div class="card-body">

        <div class="form-group">
            <label class="small mb-1" for="captchaWidth">Width</label>
            <input class="form-control py-4" id="captchaWidth" type="text"
                   placeholder="Enter captcha pixel width" value="" readonly/>
        </div>

        <div class="form-group">
            <label class="small mb-1" for="captchaHeight">Height</label>
            <input class="form-control py-4" id="captchaHeight" type="text"
                   placeholder="Enter captcha pixel height" value="" readonly/>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Security</strong>
    </div>
    <div class="card-body">

        <div class="form-group">
            <label class="small mb-1" for="captchaMinLength">Min. length</label>
            <input class="form-control py-4" id="captchaMinLength" type="text"
                   placeholder="Enter captcha minimum length" value="" readonly/>
        </div>

        <div class="form-group">
            <label class="small mb-1" for="captchaMaxLength">Max. length</label>
            <input class="form-control py-4" id="captchaMaxLength" type="text"
                   placeholder="Enter captcha maximum length" value="" readonly/>
        </div>

        <div class="form-group">
            <label class="small mb-1" for="captchaMaxRotation">Max. letter rotation</label>
            <input class="form-control py-4" id="captchaMaxRotation" type="text"
                   placeholder="Enter captcha maximum letter rotation" value="" readonly/>
        </div>


    </div>
</div>
