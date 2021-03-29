<?php

// Called from: ../../pluginConfig.php

?>
<script>

    function addCookieToHtml(cookieName, cookieObj) {
        cookieName = cookieName.replace(/[^a-zA-Z]/g, '');

        if (cookieName.trim() == '') {
            message('Empty cookie name not allowed');
            return false;
        }

        if ($('#cookieItem_' + cookieName).length != 0) {
            message('A cookie with the name [' + cookieName + '] already exists');
            return false;
        }

        var cookie =
            '<div class="card mb-4 cookieItem" cookieName="' + cookieName + '" id="cookieItem_' + cookieName + '">\n' +
            '    <div class="card-header">\n' +
            '        <strong>Cookie: ' + cookieName + '</strong>\n' +
            '    </div>\n' +
            '    <div class="card-body">\n';

        cookie +=
            '<div class="mb-4 float-right">' +
            '<button class="btn btn-secondary" type="button" onclick="$(\'#cookieItem_' + cookieName + '\').remove();">Remove cookie</button>' +
            '</div>';

        cookie +=
            '<div class="form-group">\n' +
            '  <label class="small mb-1" for="cookieRequired_' + cookieName + '">Required</label>\n' +
            '  <input type="checkbox" id="cookieRequired_' + cookieName + '" ' + (cookieObj.required === true ? 'checked' : '') + '/>\n' +
            '</div>';

        for (language in <?php print $beyond->prefix; ?>languages) {
            cookie +=
                '<div class="mb-1">' +
                '<strong>' + <?php print $beyond->prefix; ?>languages[language] + '</strong>' +
                '</div>';
            cookie +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="title_' + cookieName + '_' + language + '">Title</label>\n' +
                '  <input type="text" class="form-control py-4" id="title_' + cookieName + '_' + language + '" type="text" placeholder="Enter title for language ' + <?php print $beyond->prefix; ?>languages[language] + '" value="' + (cookieObj.title ? cookieObj.title[language] : '') + '"/>\n' +
                '</div>';
            cookie +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="info_' + cookieName + '_' + language + '">Info</label>\n' +
                '  <textarea rows=4 class="form-control" id="info_' + cookieName + '_' + language + '" type="text" placeholder="Enter info for language ' + <?php print $beyond->prefix; ?>languages[language] + '">' + cookieObj.info[language] + '</textarea>\n' +
                '</div>';
            cookie +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="privacyURL_' + cookieName + '_' + language + '">Privacy URL</label>\n' +
                '  <input type="text" class="form-control py-4" id="privacyURL_' + cookieName + '_' + language + '" type="text" placeholder="Enter privacy url for language ' + <?php print $beyond->prefix; ?>languages[language] + '" value="' + cookieObj.privacyURL[language] + '"/>\n' +
                '</div>';
        }

        cookie +=
            '    </div>\n' +
            '</div>';

        $('#cookies').append(cookie);

        return true;
    }

    function load() {
        <?php print $beyond->prefix; ?>api.cookiebox_config.load({}, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if ((typeof data.load === 'object') && (data.load !== null)) {

                    // Design
                    $('#cookieBoxBackgroundColor').val(data.load.apperence.box.backgroundColor);
                    $('#cookieBoxBackgroundColor').removeAttr('readonly');
                    $('#cookieBoxFontColor').val(data.load.apperence.box.fontColor);
                    $('#cookieBoxFontColor').removeAttr('readonly');
                    $('#cookieBoxLinkColor').val(data.load.apperence.box.linkColor);
                    $('#cookieBoxLinkColor').removeAttr('readonly');

                    // Introduction

                    var introduction =
                        '<strong>Head</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        introduction +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookieBoxText_' + language + '">Text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<textarea rows=4 class="form-control" id="cookieBoxText_' + language + '" placeholder="Enter text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '"></textarea>' +
                            '</div>';
                    }

                    introduction +=
                        '<strong>Preferd button</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        introduction +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookieBoxButtonPreferedText_' + language + '">Prefered button text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<input class="form-control py-4" id="cookieBoxButtonPreferedText_' + language + '" type="text" placeholder="Enter prefered button text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '" value="" readonly/>' +
                            '</div>';
                    }

                    introduction +=
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieBoxButtonPreferedBackgroundColor">Prefered button background color</label>' +
                        '<input class="form-control py-4" id="cookieBoxButtonPreferedBackgroundColor" type="text" placeholder="Enter prefered button background color like #000000" value="" readonly/>' +
                        '</div>' +
                        '' +
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieBoxButtonPreferedTextColor">Prefered button text color</label>' +
                        '<input class="form-control py-4" id="cookieBoxButtonPreferedTextColor" type="text" placeholder="Enter prefered button text color like #ffffff" value="" readonly/>' +
                        '</div>' +
                        '' +
                        '<strong>Button</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        introduction +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookieBoxButtonText_' + language + '">Button text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<input class="form-control py-4" id="cookieBoxButtonText_' + language + '" type="text" placeholder="Enter button text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '" value="" readonly/>' +
                            '</div>';
                    }

                    introduction +=
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieBoxButtonBackgroundColor">Button background color</label>' +
                        '<input class="form-control py-4" id="cookieBoxButtonBackgroundColor" type="text" placeholder="Enter button background color like #f0f0f0" value="" readonly/>' +
                        '</div>' +
                        '' +
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieBoxButtonTextColor">Button text color</label>' +
                        '<input class="form-control py-4" id="cookieBoxButtonTextColor" type="text" placeholder="Enter button text color like #909090" value="" readonly/>' +
                        '</div>' +
                        '' +
                        '<strong>Settings link</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        introduction +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookieSettingsLinkText_' + language + '">Settings link text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<input class="form-control py-4" id="cookieSettingsLinkText_' + language + '" type="text" placeholder="Enter setting link text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '" value="" readonly/>' +
                            '</div>';
                    }

                    introduction +=
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieSettingsLinkTextColor">Settings link text color</label>' +
                        '<input class="form-control py-4" id="cookieSettingsLinkTextColor" type="text" placeholder="Enter settings link text color like #c0c0c0" value="" readonly/>' +
                        '</div>' +
                        '<strong>Privacy link</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        introduction +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookiePrivacyLinkText_' + language + '">Privacy link text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<input class="form-control py-4" id="cookiePrivacyLinkText_' + language + '" type="text" placeholder="Enter privacy link text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '" value="" readonly/>' +
                            '</div>';
                    }

                    introduction +=
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookiePrivacyLinkTextColor">Privacy link text color</label>' +
                        '<input class="form-control py-4" id="cookiePrivacyLinkTextColor" type="text" placeholder="Enter privacy link text color like #c0c0c0" value="" readonly/>' +
                        '</div>';

                    $('#introduction').html(introduction);

                    if (typeof data.load.apperence.box.text === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxText_' + language).val(data.load.apperence.box.text[language]);
                            $('#cookieBoxText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxText_' + language).val(data.load.apperence.box.text);
                            $('#cookieBoxText_' + language).removeAttr('readonly');
                        }
                    }

                    if (typeof data.load.apperence.preferedButton.text === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxButtonPreferedText_' + language).val(data.load.apperence.preferedButton.text[language]);
                            $('#cookieBoxButtonPreferedText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxButtonPreferedText_' + language).val(data.load.apperence.preferedButton.text);
                            $('#cookieBoxButtonPreferedText_' + language).removeAttr('readonly');
                        }
                    }

                    $('#cookieBoxButtonPreferedBackgroundColor').val(data.load.apperence.preferedButton.backgroundColor);
                    $('#cookieBoxButtonPreferedBackgroundColor').removeAttr('readonly');
                    $('#cookieBoxButtonPreferedTextColor').val(data.load.apperence.preferedButton.textColor);
                    $('#cookieBoxButtonPreferedTextColor').removeAttr('readonly');

                    if (typeof data.load.apperence.button.text === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxButtonText_' + language).val(data.load.apperence.button.text[language]);
                            $('#cookieBoxButtonText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxButtonText_' + language).val(data.load.apperence.button.text);
                            $('#cookieBoxButtonText_' + language).removeAttr('readonly');
                        }
                    }

                    $('#cookieBoxButtonBackgroundColor').val(data.load.apperence.button.backgroundColor);
                    $('#cookieBoxButtonBackgroundColor').removeAttr('readonly');
                    $('#cookieBoxButtonTextColor').val(data.load.apperence.button.textColor);
                    $('#cookieBoxButtonTextColor').removeAttr('readonly');

                    if (typeof data.load.apperence.settingsLink.text === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieSettingsLinkText_' + language).val(data.load.apperence.settingsLink.text[language]);
                            $('#cookieSettingsLinkText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieSettingsLinkText_' + language).val(data.load.apperence.settingsLink.text);
                            $('#cookieSettingsLinkText_' + language).removeAttr('readonly');
                        }
                    }

                    $('#cookieSettingsLinkTextColor').val(data.load.apperence.settingsLink.textColor);
                    $('#cookieSettingsLinkTextColor').removeAttr('readonly');

                    if (typeof data.load.apperence.privacyLink.text === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookiePrivacyLinkText_' + language).val(data.load.apperence.privacyLink.text[language]);
                            $('#cookiePrivacyLinkText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookiePrivacyLinkText_' + language).val(data.load.apperence.privacyLink.text);
                            $('#cookiePrivacyLinkText_' + language).removeAttr('readonly');
                        }
                    }

                    $('#cookiePrivacyLinkTextColor').val(data.load.apperence.privacyLink.textColor);
                    $('#cookiePrivacyLinkTextColor').removeAttr('readonly');

                    // Details
                    var details =
                        ' <strong>Head</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        details +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookieBoxDetailsText_' + language + '">Text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<textarea rows=4 class="form-control" id="cookieBoxDetailsText_' + language + '" placeholder="Enter text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '"></textarea>' +
                            '</div>';
                    }

                    details +=
                        '<strong>Button</strong>';

                    for (language in <?php print $beyond->prefix; ?>languages) {
                        details +=
                            '<div class="form-group">' +
                            '<label class="small mb-1" for="cookieBoxDetailsButtonText_' + language + '">Button text [' + <?php print $beyond->prefix; ?>languages[language] + ']</label>' +
                            '<input class="form-control py-4" id="cookieBoxDetailsButtonText_' + language + '" type="text" placeholder="Enter button text for language: ' + <?php print $beyond->prefix; ?>languages[language] + '" value="" readonly/>' +
                            '</div>';
                    }

                    details +=
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieBoxDetailsButtonBackgroundColor">Button background color</label>' +
                        '<input class="form-control py-4" id="cookieBoxDetailsButtonBackgroundColor" type="text" placeholder="Enter button background color like #000000" value="" readonly/>' +
                        '</div>' +
                        '' +
                        '<div class="form-group">' +
                        '<label class="small mb-1" for="cookieBoxDetailsButtonTextColor">Button text color</label>' +
                        '<input class="form-control py-4" id="cookieBoxDetailsButtonTextColor" type="text" placeholder="Enter button text color like #ffffff" value="" readonly/>' +
                        '</div>'
                    $('#details').html(details);

                    if (typeof data.load.apperence.box.detailsText === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxDetailsText_' + language).val(data.load.apperence.box.detailsText[language]);
                            $('#cookieBoxDetailsText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxDetailsText_' + language).val(data.load.apperence.box.detailsText);
                            $('#cookieBoxDetailsText_' + language).removeAttr('readonly');
                        }
                    }

                    if (typeof data.load.apperence.detailsButton.text === 'object') {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxDetailsButtonText_' + language).val(data.load.apperence.detailsButton.text[language]);
                            $('#cookieBoxDetailsButtonText_' + language).removeAttr('readonly');
                        }
                    } else {
                        for (language in <?php print $beyond->prefix; ?>languages) {
                            $('#cookieBoxDetailsButtonText_' + language).val(data.load.apperence.detailsButton.text);
                            $('#cookieBoxDetailsButtonText_' + language).removeAttr('readonly');
                        }
                    }

                    $('#cookieBoxDetailsButtonBackgroundColor').val(data.load.apperence.detailsButton.backgroundColor);
                    $('#cookieBoxDetailsButtonBackgroundColor').removeAttr('readonly');
                    $('#cookieBoxDetailsButtonTextColor').val(data.load.apperence.detailsButton.textColor);
                    $('#cookieBoxDetailsButtonTextColor').removeAttr('readonly');

                    // Remove all cookies
                    $("#cookies").empty();

                    // Cookies
                    for (cookieName in data.load.cookies) {
                        addCookieToHtml(cookieName, data.load.cookies[cookieName]);
                    }

                } else {
                    message('Load configuration failed: ' + data.load);
                }
            }
        });
    }

    function save() {

        // Apperence
        var data = {
            'apperence': {
                'box': {
                    'backgroundColor': $('#cookieBoxBackgroundColor').val(),
                    'fontColor': $('#cookieBoxFontColor').val(),
                    'linkColor': $('#cookieBoxLinkColor').val()
                },
                'preferedButton': {
                    'backgroundColor': $('#cookieBoxButtonPreferedBackgroundColor').val(),
                    'textColor': $('#cookieBoxButtonPreferedTextColor').val()
                },
                'button': {
                    'backgroundColor': $('#cookieBoxButtonBackgroundColor').val(),
                    'textColor': $('#cookieBoxButtonTextColor').val()
                },
                'detailsButton': {
                    'backgroundColor': $('#cookieBoxDetailsButtonBackgroundColor').val(),
                    'textColor': $('#cookieBoxDetailsButtonTextColor').val()
                },
                'settingsLink': {
                    'textColor': $('#cookieSettingsLinkTextColor').val()
                },
                'privacyLink': {
                    'textColor': $('#cookiePrivacyLinkTextColor').val()
                }
            }
        };
        data.apperence.box.text = {};
        data.apperence.box.detailsText = {};
        data.apperence.preferedButton.text = {};
        data.apperence.button.text = {};
        data.apperence.detailsButton.text = {};
        data.apperence.settingsLink.text = {};
        data.apperence.privacyLink.text = {};
        for (language in <?php print $beyond->prefix; ?>languages) {
            data.apperence.box.text[language] = $('#cookieBoxText_' + language).val();
            data.apperence.box.detailsText[language] = $('#cookieBoxDetailsText_' + language).val();
            data.apperence.preferedButton.text[language] = $('#cookieBoxButtonPreferedText_' + language).val();
            data.apperence.button.text[language] = $('#cookieBoxButtonText_' + language).val();
            data.apperence.detailsButton.text[language] = $('#cookieBoxDetailsButtonText_' + language).val();
            data.apperence.settingsLink.text[language] = $('#cookieSettingsLinkText_' + language).val();
            data.apperence.privacyLink.text[language] = $('#cookiePrivacyLinkText_' + language).val();
        }

        // Get cookie information
        data.cookies = {};
        $('.cookieItem').each(function (item) {
            var cookieName = $(this).attr('cookieName');
            data.cookies[cookieName] = {};
            data.cookies[cookieName].required = $('#cookieRequired_' + cookieName + ':checked').val() != undefined;
            data.cookies[cookieName].title = {};
            for (language in <?php print $beyond->prefix; ?>languages) {
                data.cookies[cookieName].title[language] = $('#title_' + cookieName + '_' + language).val();
            }
            data.cookies[cookieName].info = {};
            for (language in <?php print $beyond->prefix; ?>languages) {
                data.cookies[cookieName].info[language] = $('#info_' + cookieName + '_' + language).val();
            }
            data.cookies[cookieName].privacyURL = {};
            for (language in <?php print $beyond->prefix; ?>languages) {
                data.cookies[cookieName].privacyURL[language] = $('#privacyURL_' + cookieName + '_' + language).val();
            }
        });

        // Send
        <?php print $beyond->prefix; ?>api.cookiebox_config.save(
            data, function (error, data) {
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

    function addCookie(fromModal = false, cookieName = '') {
        if (fromModal === false) {

            var fields = '';

            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="cookieName">Cookie name</label>\n' +
                '  <input class="form-control py-4" id="cookieName" type="text" placeholder="Enter unique cookie name here"/>\n' +
                '</div>';
            fields +=
                '<div class="form-group">\n' +
                '  <label class="small mb-1" for="cookieRequired">Required</label>\n' +
                '  <input type="checkbox" checked id="cookieRequired"/>\n' +
                '</div>';

            for (language in <?php print $beyond->prefix; ?>languages) {
                fields +=
                    '<div class="mb-1">' +
                    '<strong>' + <?php print $beyond->prefix; ?>languages[language] + '</strong>' +
                    '</div>';
                fields +=
                    '<div class="form-group">\n' +
                    '  <label class="small mb-1" for="title_' + language + '">Title</label>\n' +
                    '  <input type="text" class="form-control py-4" id="title_' + language + '" type="text" placeholder="Enter title for language ' + <?php print $beyond->prefix; ?>languages[language] + '" />\n' +
                    '</div>';
                fields +=
                    '<div class="form-group">\n' +
                    '  <label class="small mb-1" for="info_' + language + '">Info</label>\n' +
                    '  <textarea rows=4 class="form-control" id="info_' + language + '" type="text" placeholder="Enter info for language ' + <?php print $beyond->prefix; ?>languages[language] + '"></textarea>\n' +
                    '</div>';
                fields +=
                    '<div class="form-group">\n' +
                    '  <label class="small mb-1" for="privacyURL_' + language + '">Privacy URL</label>\n' +
                    '  <input type="text" class="form-control py-4" id="privacyURL_' + language + '" type="text" placeholder="Enter privacy url for language ' + <?php print $beyond->prefix; ?>languages[language] + '" />\n' +
                    '</div>';

            }

            $('#dialogAddCookie form').html(fields);
            $('#dialogAddCookie').modal('show').on('shown.bs.modal', function (e) {
                $('#cookieName').focus();
            });
            return false;
        }

        var cookie = {};
        var cookieName = $('#cookieName').val();
        cookie.required = $('#cookieRequired:checked').val() != undefined;
        cookie.title = {};
        for (language in <?php print $beyond->prefix; ?>languages) {
            cookie.title[language] = $('#title_' + language).val();
        }
        cookie.info = {};
        for (language in <?php print $beyond->prefix; ?>languages) {
            cookie.info[language] = $('#info_' + language).val();
        }
        cookie.privacyURL = {};
        for (language in <?php print $beyond->prefix; ?>languages) {
            cookie.privacyURL[language] = $('#privacyURL_' + language).val();
        }

        if (addCookieToHtml(cookieName, cookie) !== false) {
            $('#dialogAddCookie').modal('hide');
        }
    }

    //

    $(document).ready(function () {
        load();
    });
</script>

<!-- Add cookie -->
<div class="modal fade" id="dialogAddCookie" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form onsubmit="return false;">

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="button" onclick="addCookie(
                    true,
                    $('#directoryName').val()
                    );">
                    Add cookie
                </button>
            </div>
        </div>
    </div>
</div>


<div style="width: 100%;">
    <div class="mb-4 float-left" id="captchaTest">


    </div>
    <div class="mb-4 float-right">

        <button class="btn btn-secondary" type="button" onclick="addCookie();">Add cookie</button>
        <button class="btn btn-secondary" type="button" onclick="save();">Save config</button>

    </div>
</div>
<div style="clear: both;"></div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Box</strong>
    </div>
    <div class="card-body">

        <strong>Design</strong>

        <div class="form-group">
            <label class="small mb-1" for="cookieBoxBackgroundColor">Background color</label>
            <input class="form-control py-4" id="cookieBoxBackgroundColor" type="text"
                   placeholder="Enter background color like #ffffff" value="" readonly/>
        </div>

        <div class="form-group">
            <label class="small mb-1" for="cookieBoxFontColor">Font color</label>
            <input class="form-control py-4" id="cookieBoxFontColor" type="text"
                   placeholder="Enter font color like #000000" value="" readonly/>
        </div>

        <div class="form-group">
            <label class="small mb-1" for="cookieBoxLinkColor">Link color</label>
            <input class="form-control py-4" id="cookieBoxLinkColor" type="text"
                   placeholder="Enter link color like #0000ff" value="" readonly/>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Introduction</strong>
    </div>
    <div class="card-body" id="introduction">


    </div>
</div>


<div class="card mb-4">
    <div class="card-header">
        <strong>Details</strong>
    </div>
    <div class="card-body" id="details">


    </div>
</div>

<div id="cookies">
</div>

