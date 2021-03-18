<?php

// Called from: ../../pluginSite.php

// Get configured database from plugin configuration
$configJson = file_get_contents(__DIR__ . '/../../config/mail_settings.json');
$configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);
if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $beyond->db->databases))) {
    $database = $configObj->database;
} else {
    $database = $beyond->config->get('database', 'defaultDatabase');
}

?>
<script>

    // -------------------------------------------------------------------------------------------------------------

    var viewRowsPerPage = 100;
    var database = <?php print json_encode($database); ?>;
    var table = <?php print json_encode($beyond->prefix . 'mail_data'); ?>;

    var currentPage = 1;

    function pageLink(pageNo, currentPage) {
        return (currentPage == pageNo ? '<strong>' : '<span style="cursor:pointer;" onclick="loadPage(' + pageNo.toString() + ');">') +
            pageNo +
            (currentPage == pageNo ? '</strong>' : '</span>') + ' ';
    }

    function loadPage(loadPage) {
        $('#viewCells tbody').empty();

        <?php print $beyond->prefix; ?>api.tables.rowCount({
            'database': database,
            'table': table
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.rowCount !== false) {

                    var pageNumbers = '';
                    var pages = Math.ceil(data.rowCount / viewRowsPerPage);
                    viewCurrentPage = loadPage;

                    if (pages > 6) {

                        // First page
                        pageNumbers += pageLink(1, loadPage);
                        pageNumbers += pageLink(2, loadPage);
                        pageNumbers += pageLink(3, loadPage);

                        if (loadPage > 5) {
                            // 1 2 3 ...  5 [6] 7
                            pageNumbers += ' ... ';
                        }

                        // Current page
                        if ((loadPage - 1 > 3) && (loadPage - 1 < pages - 2)) {
                            pageNumbers += pageLink(loadPage - 1, loadPage);
                        }
                        if ((loadPage > 3) && (loadPage < pages - 2)) {
                            pageNumbers += pageLink(loadPage, loadPage);
                        }
                        if ((loadPage + 1 > 3) && (loadPage + 1 < pages - 2)) {
                            pageNumbers += pageLink(loadPage + 1, loadPage);
                        }

                        // Last page
                        if (loadPage + 2 < pages - 2) {
                            // ... 6 [7] 8 ... 10 11 12
                            pageNumbers += ' ... ';
                        } else {
                            // 7 [8] 9 10 11 12
                            // 7 8 [9] 10 11 12
                            // 7 8 9 [10] 11 12
                        }

                        pageNumbers += pageLink(pages - 2, loadPage);
                        pageNumbers += pageLink(pages - 1, loadPage);
                        pageNumbers += pageLink(pages, loadPage);

                    } else {
                        // 1 2 3 4 5 6
                        for (var i = 1; i <= pages; i++) {
                            pageNumbers += pageLink(i, loadPage);
                        }
                    }

                    if (pageNumbers == '') {
                        pageNumbers = 1;
                    }
                    $('#viewPagination').html('Pages: ' + pageNumbers);
                    loadPageData(loadPage);

                } else {
                    message('Fetch row count failed');
                }
            }
        });
    }

    function loadPageData(loadPage) {
        currentPage = loadPage;

        <?php print $beyond->prefix; ?>api.tables.loadData({
            'database': database,
            'table': table,
            'offset': ((loadPage - 1) * viewRowsPerPage),
            'limit': viewRowsPerPage,
            'order': 'id:desc'
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.loadData !== false) {
                    for (rowNo in data.loadData) {
                        var mailData = btoa(JSON.stringify(data.loadData[rowNo]));

                        var cols = '';
                        cols += '<td>' + data.loadData[rowNo]['from'] + '</td>';
                        cols += '<td>' + data.loadData[rowNo]['to'] + '</td>';
                        cols += '<td>' + data.loadData[rowNo]['subject'] + '</td>';
                        cols += '<td nowrap>';
                        cols += '  <i class="fas fa-eye ml-1" style="cursor:pointer;" onclick="viewMail(\'' + mailData + '\');"></i>';
                        cols += '  <i class="fas fa-trash ml-1" style="cursor:pointer;" onclick="deleteMail(\'' + mailData + '\');"></i>';
                        cols += '</td>';

                        $('#viewCells tbody').append('<tr>' + cols + '</tr>');
                    }
                } else {
                    message('Fetch rows from table failed');
                }
            }
        });
    }

    function viewMail(jsonData) {
        var out = '';
        var data = JSON.parse(atob(jsonData));

        if (data.from != '') {
            out += '<div>';
            out += 'Timestamp sent: ' + data.date;
            out += '</div>';
        }

        if (data.from != '') {
            out += '<div>';
            out += 'From: ' + data.from;
            out += '</div>';
        }

        if (data.to != '') {
            out += '<div>';
            out += 'To: ' + data.to;
            out += '</div>';
        }

        if (data.replyTo != '') {
            out += '<div>';
            out += 'Reply to: ' + data.replyTo;
            out += '</div>';
        }

        if (data.bcc != '') {
            out += '<div>';
            out += 'BCC: ' + data.bcc;
            out += '</div>';
        }

        if (data.subject != '') {
            out += '<div>';
            out += 'Subject: ' + data.subject;
            out += '</div>';
        }

        out += '<div style="border-top:1px solid black; margin-top:20px; padding-top:20px;">';
        if (data.kind === 'html') {
            out += data.mail;
        } else {
            out += '<pre>' + data.mail + '</pre>';
        }
        out += '</div>';

        $('#list').hide();
        $('#details').show();
        $('#mail').html(out);
    }


    function deleteMail(jsonData, fromModal = false) {
        if (fromModal === false) {
            $('#dialogDeleteMail .modal-body').html('<div class="mb-4">Delete mail from database?</b></div>');
            $('#dialogDeleteMail').data('jsonData', jsonData).modal('show');
            return false;
        }

        var out = '';
        var data = JSON.parse(atob(jsonData));

        <?php print $beyond->prefix; ?>api.tables.deleteData({
            'database': database,
            'table': table,
            'primary': 'id',
            'value': data.id
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error);
            } else {
                if (data.deleteData !== false) {
                    $('#dialogDeleteMail').modal('hide');
                    loadPage(currentPage);
                } else {
                    message('Deleting mail failed');
                }
            }
        });

    }

    // -------------------------------------------------------------------------------------------------------------

    $(function () {
        loadPage(1);
    });

</script>

<!-- Delete row from table -->
<div class="modal fade" id="dialogDeleteMail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">

                ...

            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button"
                        onclick="deleteMail(
                                $('#dialogDeleteMail').data('jsonData'),
                                true
                                );">
                    Delete mail
                </button>
                <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div style="width: 100%;" id="list">

    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover table-striped" id="viewCells">
            <thead>
            <th width="1%" style="min-width:100px;">From</th>
            <th width="1%" style="min-width:100px;">To</th>
            <th>Subject</th>
            <th width="1%"></th>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div id="viewPagination" class="mb-4">

    </div>
</div>

<div style="width: 100%; display:none;" id="details">

    <div class="mb-4 float-right">

        <button class="btn btn-secondary"
                type="button"
                onclick="$('#details').hide();$('#list').show();">
            Close
        </button>

    </div>

    <div style="clear: both;"></div>

    <div id="mail">

    </div>

</div>