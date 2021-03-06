<?php

// Called from: ../../pluginConfig.php

?>
<script>

    function load() {
        <?php print $beyond->prefix; ?>api.blocks_config.load({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.load === 'object') && (data.load !== null)) {

                    $('#blocksDatabase').val(data.load.database).change();
                    $('#blocksDatabase').removeAttr('readonly');

                } else {
                    message('Load configuration failed: ' + data.load);
                }
            }
        });
    }

    function save() {
        // Send
        <?php print $beyond->prefix; ?>api.blocks_config.save({
            'database': $('#blocksDatabase').val()
        }, function (error, data) {
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
            <label class="small mb-1" for="blocksDatabase">Database</label>
            <select class="form-control" id="blocksDatabase">
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

