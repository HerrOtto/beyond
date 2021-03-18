<?php

// Called from: ../../pluginConfig.php

?>
<script>

    function load() {
        <?php print $beyond->prefix; ?>api.mail_config.load({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.load === 'object') && (data.load !== null)) {

                    $('#mailDatabase').val(data.load.database).change();
                    $('#mailDatabase').removeAttr('readonly');

                    for (lang in beyond_languages) {
                        var out = '';

                        out += '<div class="card mb-4">';
                        out += '    <div class="card-header">';
                        out += '        <strong>Settings [' + beyond_languages[lang] + ']</strong>';
                        out += '    </div>';
                        out += '    <div class="card-body">';
                        out += '        <strong>Subject</strong>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailSubjectPrefix_' + lang + '">Subject prefix</label>';
                        out += '           <input class="form-control py-4" id="mailSubjectPrefix_' + lang + '" type="text"';
                        out += '                   placeholder="Enter subject prefix" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailSubjectSuffix_' + lang + '">Subject suffix</label>';
                        out += '            <input class="form-control py-4" id="mailSubjectSuffix_' + lang + '" type="text"';
                        out += '                   placeholder="Enter subject suffix" value="" readonly/>';
                        out += '        </div>';
                        out += '        <strong>Recipient/Sender</strong>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailFrom_' + lang + '">From</label>';
                        out += '            <input class="form-control py-4" id="mailFrom_' + lang + '" type="text"';
                        out += '                   placeholder="Enter sender here" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailReplyTo_' + lang + '">Reply to</label>';
                        out += '           <input class="form-control py-4" id="mailReplyTo_' + lang + '" type="text"';
                        out += '                   placeholder="Enter reply address here" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailTo_' + lang + '">To</label>';
                        out += '           <input class="form-control py-4" id="mailTo_' + lang + '" type="text"';
                        out += '                   placeholder="Enter default recipient here" value="" readonly/>';
                        out += '        </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailBcc_' + lang + '">BCC</label>';
                        out += '            <input class="form-control py-4" id="mailBcc_' + lang + '" type="text"';
                        out += '                   placeholder="Enter BCC value here" value="" readonly/>';
                        out += '       </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailFootetText_' + lang + '">Footer (Text mail)</label>';
                        out += '            <textarea rows=6 class="form-control" id="mailFootetText_' + lang + '" type="text" readonly></textarea>';
                        out += '       </div>';
                        out += '        <div class="form-group">';
                        out += '            <label class="small mb-1" for="mailFootetHtml_' + lang + '">Footer (HTML mail)</label>';
                        out += '            <textarea rows=6 class="form-control" id="mailFootetHtml_' + lang + '" type="text" readonly></textarea>';
                        out += '       </div>';
                        out += '    </div>';
                        out += '</div>';

                        $('#defaults').append(out);

                        $('#mailSubjectPrefix_' + lang).val(data.load['settings_' + lang].subjectPrefix);
                        $('#mailSubjectPrefix_' + lang).removeAttr('readonly');
                        $('#mailSubjectSuffix_' + lang).val(data.load['settings_' + lang].subjectSuffix);
                        $('#mailSubjectSuffix_' + lang).removeAttr('readonly');
                        $('#mailFrom_' + lang).val(data.load['settings_' + lang].from);
                        $('#mailFrom_' + lang).removeAttr('readonly');
                        $('#mailTo_' + lang).val(data.load['settings_' + lang].to);
                        $('#mailTo_' + lang).removeAttr('readonly');
                        $('#mailReplyTo_' + lang).val(data.load['settings_' + lang].replyTo);
                        $('#mailReplyTo_' + lang).removeAttr('readonly');
                        $('#mailBcc_' + lang).val(data.load['settings_' + lang].bcc);
                        $('#mailBcc_' + lang).removeAttr('readonly');
                        $('#mailFootetText_' + lang).val(data.load['settings_' + lang].footerText);
                        $('#mailFootetText_' + lang).removeAttr('readonly');
                        $('#mailFootetHtml_' + lang).val(data.load['settings_' + lang].footerHtml);
                        $('#mailFootetHtml_' + lang).removeAttr('readonly');

                    }

                } else {
                    message('Load configuration failed: ' + data.load);
                }
            }
        });
    }

    function save() {
        var data = {
            'database': $('#mailDatabase').val()
        };
        for (lang in beyond_languages) {
            data['settings_' + lang] = {
                'subjectPrefix': $('#mailSubjectPrefix_' + lang).val(),
                'subjectSuffix': $('#mailSubjectSuffix_' + lang).val(),
                'from': $('#mailFrom_' + lang).val(),
                'to': $('#mailTo_' + lang).val(),
                'replyTo': $('#mailReplyTo_' + lang).val(),
                'bcc': $('#mailBcc_' + lang).val(),
                'footerText': $('#mailFootetText_' + lang).val(),
                'footerHtml': $('#mailFootetHtml_' + lang).val()
            }
        }
        // Send
        <?php print $beyond->prefix; ?>api.mail_config.save(data,
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
            <label class="small mb-1" for="mailDatabase">Database</label>
            <select class="form-control" id="mailDatabase">
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
