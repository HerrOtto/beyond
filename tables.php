<?php

header('Content-type: text/html; Charset=UTF-8');

include_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    // Is not admin or viewer
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

/*
foreach (range(0, 100000) as $i) {
    $res = $db->databases['net']->insert('test', array(
        'id' => time(),
        'no' => 'test' . rand()
    ));
}
*/

?>
<html>
<head>
    <title>Tables</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>

    <style>

        .database {
            font-weight: bold;
            border-bottom: 1px solid gray;
            padding: 4px;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .tableItem {
            display: table;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 4px;
            cursor: pointer;
        }

        .tableItem:hover {
            border: 1px solid #c0c0c0;
            background-color: #f0f0f0;
        }

        .tableItemIcon {
            display: table-cell;
            padding-left: 5px;
            padding-right: 5px;
            width: 1%;
        }

        .tableItemName {
            display: table-cell;
            width: 98%;
        }

        .tableItemAction {
            display: table-cell;
            width: 1%;
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>

    <script>

        function tablesFetch() {
            <?php print $beyond->prefix; ?>api.beyondTables.fetch({
                //
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (typeof data.fetch === 'object') {
                        var out = '';

                        for (database in data.fetch) {
                            out += '<div class="database">';
                            out += database;
                            out += '</div>'

                            out += '<div class="text-right mb-4">';
                            out += '<button class="btn btn-secondary" type="button" onclick="tablesCreate(\'' + base64encode(database) + '\', false);">Create table';
                            out += '</button>';
                            out += '</div>';

                            for (table in data.fetch[database]) {

                                // Check if table is internal
                                var internal = false;
                                if (database === data.defaultDatabase) {
                                    if (data.internalTables.indexOf(data.fetch[database][table]) > -1) {
                                        internal = true;
                                    }
                                }

                                out += '<div class="tableItem">';
                                out += '<span class="tableItemIcon">';
                                out += '<i class="fas fa-table"></i>';
                                out += '</span>';
                                if (!internal) {
                                    out += '<span class="tableItemName" onclick="tablesView(\'' + base64encode(database) + '\', \'' + base64encode(data.fetch[database][table]) + '\', false);">';
                                } else {
                                    out += '<span class="tableItemName" onclick="tablesView(\'' + base64encode(database) + '\', \'' + base64encode(data.fetch[database][table]) + '\', true);">';
                                }
                                out += data.fetch[database][table];
                                out += '</span>';

                                out += '<span class="tableItemAction text-nowrap">';

                                if (!internal) {
                                    out += '  <i class="fas fa-pen ml-1" onclick="tablesEdit(\'' + base64encode(database) + '\', \'' + base64encode(data.fetch[database][table]) + '\', false);"></i>';
                                    out += '  <i class="fas fa-trash ml-1" onclick="tablesDrop(\'' + base64encode(database) + '\',\'' + base64encode(data.fetch[database][table]) + '\');"></i>';
                                } else {
                                    out += '  <i class="fas fa-eye ml-1" onclick="tablesEdit(\'' + base64encode(database) + '\', \'' + base64encode(data.fetch[database][table]) + '\', true);"></i>';
                                }

                                out += '</span>';
                                out += '</div>'
                            }
                        }
                        $('#databases').html(
                            out
                        );
                    } else {
                        message('Fetching table list failed');
                    }
                }
            });
        }

        function tablesDrop(databaseBase64, tableBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogTablesDrop .modal-body').html('Drop table <b>' + base64decode(tableBase64) + '</b> from database <b>' + base64decode(databaseBase64) + '</b>');
                $('#dialogTablesDrop').data('table', base64decode(tableBase64)).data('database', base64decode(databaseBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.beyondTables.drop({
                'database': base64decode(databaseBase64),
                'table': base64decode(tableBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.drop === true) {
                        $('#dialogTablesDrop').modal('hide');
                        tablesFetch();
                    } else {
                        message('Drop table [' + base64decode(tableBase64) + '] from database [' + base64decode(databaseBase64) + '] failed: ' + data.drop);
                    }
                }
            });
        }

        function tablesCreate(databaseBase64, fromModal = false, tableName, fieldName, kind, index, allowNull, defaultValue) {
            if (fromModal === false) {
                $('#dialogTablesCreateTitle').html('Create table in database <b>' + base64decode(databaseBase64) + '</b>');
                $('#addTableName').val('');
                $('#addTableColumnName').val('');
                $('#addTableColumnKind').val('string').change();
                $('#addTableColumnNull').prop('checked', true);
                $('#addTableColumnDefault').val('');
                $('#addTableColumnIndex').val('').change();
                $('#dialogTablesCreate').data('database', base64decode(databaseBase64)).modal('show').on('shown.bs.modal', function (e) {
                    $('#addTableName').focus();
                });
                return false;
            }

            var fields = [];

            <?php print $beyond->prefix; ?>api.beyondTables.create({
                'database': base64decode(databaseBase64),
                'table': tableName,
                'fields': {
                    [fieldName]: {
                        'kind': kind,
                        'index': index,
                        'null': allowNull,
                        'default': defaultValue
                    }
                }
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.create === true) {
                        $('#dialogTablesCreate').modal('hide');
                        tablesFetch();
                    } else {
                        message('Create table [' + tableName + '] in database [' + base64decode(databaseBase64) + '] failed: ' + data.create);
                    }
                }
            });
        }

        // -------------------------------------------------------------------------------------------------------------

        var editDatabase = false;
        var editTable = false;

        function tablesEdit(databaseBase64, tableBase64, internalTable) {
            $('#editCells tbody').empty();
            editDatabase = false;
            editTable = false;

            <?php print $beyond->prefix; ?>api.beyondTables.info({
                'database': base64decode(databaseBase64),
                'table': base64decode(tableBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.info !== false) {

                        $('#databases').hide();
                        $('#edit').show();
                        if (internalTable) {
                            $('#buttonEditTable').hide();
                        } else {
                            $('#buttonEditTable').show();
                        }
                        editDatabase = base64decode(databaseBase64);
                        editTable = base64decode(tableBase64);
                        for (fieldIndex in data.info) {
                            tablesEditAddField(
                                fieldIndex,  // name
                                data.info[fieldIndex].kind,
                                (data.info[fieldIndex].index ? data.info[fieldIndex].index : ''),
                                (data.info[fieldIndex].null ? data.info[fieldIndex].null : false),
                                (data.info[fieldIndex].default ? data.info[fieldIndex].default : ''),
                                Object.keys(data.info).length,
                                internalTable
                            );
                        }
                    } else {
                        message('Fetch table [' + base64decode(tableBase64) + '] details from database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesEditAddField(fieldName, kind, index, allowNull, defaultValue, fieldCount, internalTable) {
            var html =
                '<tr>' +
                '  <td>' + fieldName + '</td>' +
                '  </td>' +
                '  <td>' + kind + '</td>' +
                '  </td>' +
                '  <td>' +
                '    ' + (allowNull === true ? '<i class="fas fa-check"></i>' : '') +
                '  </td>' +
                '  <td>' +
                '    ' + defaultValue +
                '  </td>' +
                '  <td>' + index + '</td>' +
                '  <td align=center nowrap>' +
                (
                    ((fieldCount < 2) || (internalTable === true)) ? '' :
                        '<i class="fas fa-trash mt-1" style="cursor:pointer;" onclick="tablesColumnDrop(\'' + base64encode(fieldName) + '\');"></i>'
                ) +
                '  </td>' +
                '</tr>';

            $('#editCells tbody').append(html);
        }

        function tablesEditClose() {
            $('#edit').hide();
            $('#databases').show();
            editDatabase = false;
            editTable = false;
        }

        function tablesColumnAdd(fieldName, kind, index, allowNull, defaultValue) {
            var data = {
                'database': editDatabase,
                'table': editTable,
                'field': fieldName,
                'kind': kind,
                'index': index,
                'null': allowNull,
                'default': defaultValue
            };
            <?php print $beyond->prefix; ?>api.beyondTables.columnAdd(data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.columnAdd === true) {
                        $('#dialogTablesAddField').modal('hide');
                        tablesEdit(base64encode(editDatabase), base64encode(editTable), false);
                    } else {
                        message('Add column to table [' + editTable + '] of database [' + editDatabase + '] failed: ' + data.columnAdd);
                    }
                }
            });
        }

        function tablesColumnDrop(fieldNameBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogTablesDropField .modal-body').html('Drop column <b>' + base64decode(fieldNameBase64) + '</b> of table <b>' + editTable + '</b> in database <b>' + editDatabase + '</b>');
                $('#dialogTablesDropField').data('fieldName', base64decode(fieldNameBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.beyondTables.columnDrop({
                'database': editDatabase,
                'table': editTable,
                'field': base64decode(fieldNameBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.columnDrop === true) {
                        $('#dialogTablesDropField').modal('hide');
                        tablesEdit(base64encode(editDatabase), base64encode(editTable), false);
                    } else {
                        message('Drop column [' + base64decode(fieldNameBase64) + '] from table [' + editTable + '] of database [' + editDatabase + '] failed: ' + data.columnDrop);
                    }
                }
            });
        }

        // -------------------------------------------------------------------------------------------------------------

        var viewDatabase = false;
        var viewTable = false;
        var viewRowsPerPage = 100;
        var viewColumns = [];
        var viewPrimaryColumn = '';
        var viewPrimaryKind = '';
        var viewAutoColumn = '';
        var viewFields = {};
        var viewCurrentPage = 1;
        var viewInternalTable = false;

        function tablesView(databaseBase64, tableBase64, internalTable) {
            $('#editCells tbody').empty();
            viewDatabase = false;
            viewTable = false;
            viewCurrentPage = 1;
            viewPrimaryColumn = '';
            viewAutoColumn = '';
            viewPrimaryKind = '';
            viewInternalTable = internalTable;

            <?php print $beyond->prefix; ?>api.beyondTables.info({
                'database': base64decode(databaseBase64),
                'table': base64decode(tableBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.info !== false) {

                        $('#databases').hide();
                        $('#view').show();
                        if (internalTable === true) {
                            $('#buttonAddRow').hide();
                        } else {
                            $('#buttonAddRow').show();
                        }
                        viewDatabase = base64decode(databaseBase64);
                        viewTable = base64decode(tableBase64);

                        $('#viewCells thead tr').empty();

                        viewColumns = [];
                        viewFields = data.info;
                        var primary = false;
                        var unique = false;
                        var auto = false;
                        for (fieldIndex in data.info) {
                            primary = false;
                            unique = false;
                            auto = false;
                            if (data.info[fieldIndex].index) {
                                if (data.info[fieldIndex].index === 'auto') {
                                    viewAutoColumn = fieldIndex;
                                    auto = true;
                                } else if (data.info[fieldIndex].index === 'primary') {
                                    viewPrimaryColumn = fieldIndex;
                                    viewPrimaryKind = data.info[fieldIndex].kind;
                                    primary = true;
                                } else if (data.info[fieldIndex].index === 'unique') {
                                    unique = true;
                                }
                            }
                            viewColumns.push(fieldIndex);
                            $('#viewCells thead tr').append(
                                '<th nowrap>' + fieldIndex + (auto ? ' <i class="fas fa-bomb ml-1"></i>' : primary ? ' <i class="fas fa-key ml-1"></i>' : unique ? ' <i class="fas fa-star ml-1"></i>' : '') + '</th>'
                            );
                        }

                        $('#viewCells thead tr').append(
                            '<th width="1%" nowrap>Options</th>'
                        );

                        tablesViewLoadTable(1);
                    } else {
                        message('Fetch table [' + base64decode(tableBase64) + '] details from database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewPageLink(pageNo, currentPage) {
            return (currentPage == pageNo ? '<strong>' : '<span style="cursor:pointer;" onclick="tablesViewLoadTable(' + pageNo.toString() + ');">') +
                pageNo +
                (currentPage == pageNo ? '</strong>' : '</span>') + ' ';
        }

        function tablesViewLoadTable(loadPage) {
            $('#viewCells tbody').empty();

            <?php print $beyond->prefix; ?>api.beyondTables.rowCount({
                'database': viewDatabase,
                'table': viewTable
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
                            pageNumbers += tablesViewPageLink(1, loadPage);
                            pageNumbers += tablesViewPageLink(2, loadPage);
                            pageNumbers += tablesViewPageLink(3, loadPage);

                            if (loadPage > 5) {
                                // 1 2 3 ...  5 [6] 7
                                pageNumbers += ' ... ';
                            }

                            // Current page
                            if ((loadPage - 1 > 3) && (loadPage - 1 < pages - 2)) {
                                pageNumbers += tablesViewPageLink(loadPage - 1, loadPage);
                            }
                            if ((loadPage > 3) && (loadPage < pages - 2)) {
                                pageNumbers += tablesViewPageLink(loadPage, loadPage);
                            }
                            if ((loadPage + 1 > 3) && (loadPage + 1 < pages - 2)) {
                                pageNumbers += tablesViewPageLink(loadPage + 1, loadPage);
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

                            pageNumbers += tablesViewPageLink(pages - 2, loadPage);
                            pageNumbers += tablesViewPageLink(pages - 1, loadPage);
                            pageNumbers += tablesViewPageLink(pages, loadPage);

                        } else {
                            // 1 2 3 4 5 6
                            for (var i = 1; i <= pages; i++) {
                                pageNumbers += tablesViewPageLink(i, loadPage);
                            }
                        }

                        if (pageNumbers == '') {
                            pageNumbers = 1;
                        }
                        $('#viewPagination').html('Pages: ' + pageNumbers);
                        tablesViewLoadTableData(loadPage);

                    } else {
                        message('Fetch row count from table [' + base64decode(tableBase64) + '] of database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewLoadTableData(loadPage) {
            <?php print $beyond->prefix; ?>api.beyondTables.loadData({
                'database': viewDatabase,
                'table': viewTable,
                'offset': ((loadPage - 1) * viewRowsPerPage),
                'limit': viewRowsPerPage
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.loadData !== false) {
                        for (rowNo in data.loadData) {
                            var cols = '';
                            var keyValue = '';
                            for (fieldName in viewColumns) {
                                if (viewAutoColumn === viewColumns[fieldName]) {
                                    keyValue = data.loadData[rowNo][viewColumns[fieldName]];
                                }
                                if (viewPrimaryColumn === viewColumns[fieldName]) {
                                    keyValue = data.loadData[rowNo][viewColumns[fieldName]];
                                }
                                cols += '<td>' + data.loadData[rowNo][viewColumns[fieldName]] + '</td>';
                            }
                            var rowData = base64encode(JSON.stringify(data.loadData[rowNo]));
                            cols += '<td align=center nowrap>';
                            if (((viewAutoColumn !== '') || (viewPrimaryColumn !== '')) && (viewInternalTable !== true)) {
                                cols += '<i class="fas fa-pen mt-1" style="cursor:pointer;" onclick="console.log(); tablesViewRowEdit(\'' + base64encode(keyValue) + '\', \'' + rowData + '\');"></i>';
                                cols += '<i class="fas fa-trash mt-1 ml-2" style="cursor:pointer;" onclick="tablesViewRowDelete(\'' + base64encode(keyValue) + '\', false);"></i>';
                            }
                            cols += '</td>';
                            $('#viewCells tbody').append('<tr>' + cols + '</tr>');
                        }
                    } else {
                        message('Fetch rows from table [' + base64decode(tableBase64) + '] of database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewClose() {
            $('#view').hide();
            $('#databases').show();
            viewDatabase = false;
            viewTable = false;
            viewCurrentPage = 1;
        }

        function tablesViewRowAdd(fromModal = false) {
            if (fromModal === false) {
                var data = '';
                console.log(viewFields);
                for (field in viewFields) {
                    data +=
                        '<div class="form-group mb-4">' +
                        '<label class="small mb-1" for="addRowField_' + field + '">' + field + (
                            viewFields[field].index === 'auto' ? ' <i class="fas fa-bomb ml-1"></i>' :
                                viewFields[field].index === 'primary' ? ' <i class="fas fa-key ml-1"></i>' :
                                    ''
                        ) + '</label>' +
                        '<input class="form-control py-4" id="addRowField_' + field + '" type="text" ' + (
                            viewFields[field].index === 'auto' ? ' disabled' :
                                ''
                        ) + ' />' +
                        '</div>';
                }
                $('#dialogViewRowAdd .modal-body').html(
                    '<div class="mb-4">Add row to table <b>' + viewTable + '</b> in database <b>' + viewDatabase + '</b></div>' +
                    data
                );
                $('#dialogViewRowAdd').modal('show');
                return false;
            }

            fields = {};
            for (field in viewFields) {
                if (viewFields[field].index !== 'auto') {
                    fields[field] = $('#addRowField_' + field).val();
                }
            }

            <?php print $beyond->prefix; ?>api.beyondTables.addData({
                'database': viewDatabase,
                'table': viewTable,
                'fields': fields
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.addData !== false) {
                        $('#dialogViewRowAdd').modal('hide');
                        tablesViewLoadTable(viewCurrentPage);
                    } else {
                        message('Add rows to table [' + base64decode(tableBase64) + '] of database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewRowEdit(primaryValueBase64 = false, dataJsonBase64 = false) {
            if (dataJsonBase64 !== false) {
                dataJson = JSON.parse(base64decode(dataJsonBase64));

                var data = '';
                for (field in viewFields) {
                    data +=
                        '<div class="form-group mb-4">' +
                        '<label class="small mb-1" for="editRowField_' + field + '">' + field + (viewAutoColumn === field ? ' <i class="fas fa-bomb"></i> ' : viewPrimaryColumn === field ? ' <i class="fas fa-key"></i> ' : '') + '</label>' +
                        '<input class="form-control py-4" id="editRowField_' + field + '" type="text" />' +
                        '</div>';
                }
                $('#dialogViewRowEdit .modal-body').html(
                    '<div class="mb-4">Modify row with primary key <b>' + base64decode(primaryValueBase64) + '</b> from table <b>' + viewTable + '</b> in database <b>' + viewDatabase + '</b></div>' +
                    data
                );
                $('#dialogViewRowEdit').data('primaryValue', base64decode(primaryValueBase64)).modal('show');

                for (field in viewFields) {
                    if (viewAutoColumn === field) {
                        $('#editRowField_' + field).attr('disabled', true);
                    } else {
                        $('#editRowField_' + field).removeAttr('disabled');
                    }
                    $('#editRowField_' + field).val(dataJson[field]);
                }

                return false;
            }

            fields = {};
            for (field in viewFields) {
                if (viewAutoColumn !== field) {
                    fields[field] = $('#editRowField_' + field).val();
                }
            }

            var primaryColumn = viewAutoColumn !== '' ? viewAutoColumn : viewPrimaryColumn !== ''
            viewPrimaryColumn : '';
            if (primaryColumn === '') {
                return;
            }

            <?php print $beyond->prefix; ?>api.beyondTables.modifyData({
                'database': viewDatabase,
                'table': viewTable,
                'fields': fields,
                'primary': primaryColumn,
                'kind': (viewAutoColumn !== '' ? 'number' : viewPrimaryKind),
                'value': base64decode(primaryValueBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.modifyData !== false) {
                        $('#dialogViewRowEdit').modal('hide');
                        tablesViewLoadTable(viewCurrentPage);
                    } else {
                        message('Modify rows with primary key [' + base64decode(primaryValueBase64) + '] from table [' + base64decode(tableBase64) + '] of database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewRowDelete(primaryValueBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogViewRowDelete .modal-body').html('<div class="mb-4">Delete row with primary key <b>' + base64decode(primaryValueBase64) + '</b> from table <b>' + viewTable + '</b> in database <b>' + viewDatabase + '</b></div>');
                $('#dialogViewRowDelete').data('primaryValue', base64decode(primaryValueBase64)).modal('show');
                return false;
            }

            var primaryColumn;
            if (viewAutoColumn !== '') {
                primaryColumn = viewAutoColumn;
            } else if (viewPrimaryColumn !== '') {
                primaryColumn = viewPrimaryColumn;
            }
            if (primaryColumn === '') {
                return;
            }

            <?php print $beyond->prefix; ?>api.beyondTables.deleteData({
                'database': viewDatabase,
                'table': viewTable,
                'primary': primaryColumn,
                'value': base64decode(primaryValueBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.deleteData !== false) {
                        $('#dialogViewRowDelete').modal('hide');
                        tablesViewLoadTable(viewCurrentPage);
                    } else {
                        message('Add rows to table [' + base64decode(tableBase64) + '] of database [' + base64decode(databaseBase64) + '] failed');
                    }
                }
            });
        }

        // -------------------------------------------------------------------------------------------------------------

        $(function () {
            tablesFetch();
        });

    </script>
</head>
<body class="sb-nav-fixed">
<?php include_once __DIR__ . '/inc/begin.php'; ?>
<?php include_once __DIR__ . '/inc/menuTop.php'; ?>
<div id="layoutSidenav">

    <!-- Create table -->
    <div class="modal fade" id="dialogTablesCreate" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    <div id="dialogTablesCreateTitle" class="mb-4"></div>

                    <div class="form-group mb-4">
                        <label class="small mb-1" for="addTableName">Table name</label>
                        <input class="form-control py-4" id="addTableName" type="text"
                               placeholder="Enter table name here"/ />
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addTableColumnName">Column name</label>
                        <input class="form-control py-4" id="addTableColumnName" type="text"
                               placeholder="Enter column name here"/ />
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addTableColumnIndex">Index</label>
                        <select class="form-control" id="addTableColumnIndex" onchange="if ($(this).val() === 'auto') {
                            $('#addTableColumnKind').val('number').change();
                            $('#addTableColumnKind').attr('disabled', true);
                            $('#addTableColumnNull').attr('checked', true);
                            $('#addTableColumnNull').attr('disabled', true);
                            $('#addTableColumnDefault').val('');
                            $('#addTableColumnDefault').attr('disabled', true);
                        } else {
                            $('#addTableColumnKind').removeAttr('disabled');
                            $('#addTableColumnNull').removeAttr('disabled');
                            $('#addTableColumnDefault').removeAttr('disabled');
                        }">
                            <option val="auto">auto</option>
                            <option val="primary">primary</option>
                            <option val="unique">unique</option>
                            <option selected></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addTableColumnKind">Column type</label>
                        <select class="form-control" id="addTableColumnKind">
                            <option selected val="string">string</option>
                            <option val="longtext">longtext</option>
                            <option val="decimal">decimal</option>
                            <option val="number">number</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addTableColumnNull">Allow NULL</label>
                        <input type="checkbox" checked id="addTableColumnNull"/>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addTableColumnDefault">Default value</label>
                        <input class="form-control py-4" id="addTableColumnDefault" type="text"
                               placeholder="Enter default value here"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="tablesCreate(
                                base64encode($('#dialogTablesCreate').data('database')),
                                true,
                                $('#addTableName').val(),
                                $('#addTableColumnName').val(),
                                $('#addTableColumnKind').children('option:selected').val(),
                                $('#addTableColumnIndex').children('option:selected').val(),
                                $('#addTableColumnNull:checked').val() != undefined,
                                $('#addTableColumnDefault').val()
                                );">

                        Create table
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add field to table -->
    <div class="modal fade" id="dialogTablesAddField" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    <div class="form-group">
                        <label class="small mb-1" for="addColumnName">Column name</label>
                        <input class="form-control py-4" id="addColumnName" type="text"
                               placeholder="Enter column name here"/ />
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addColumnIndex">Index</label>
                        <select class="form-control" id="addColumnIndex" onchange="if ($(this).val() === 'auto') {
                            $('#addColumnKind').val('number').change();
                            $('#addColumnKind').attr('disabled', true);
                            $('#addColumnNull').attr('checked', true);
                            $('#addColumnNull').attr('disabled', true);
                            $('#addColumnDefault').val('');
                            $('#addColumnDefault').attr('disabled', true);
                        } else {
                            $('#addColumnKind').removeAttr('disabled');
                            $('#addColumnNull').removeAttr('disabled');
                            $('#addColumnDefault').removeAttr('disabled');
                        }"
                        <option val="primary">primary</option>
                        <option val="unique">unique</option>
                        <option selected></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addColumnKind">Column type</label>
                        <select class="form-control" id="addColumnKind">
                            <option selected val="longtext">longtext</option>
                            <option val="string">string</option>
                            <option val="decimal">decimal</option>
                            <option val="number">number</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addColumnNull">Allow NULL</label>
                        <input type="checkbox" checked id="addColumnNull"/>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" for="addColumnDefault">Default value</label>
                        <input class="form-control py-4" id="addColumnDefault" type="text"
                               placeholder="Enter default value here"/>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="tablesColumnAdd(
                                $('#addColumnName').val(),
                                $('#addColumnKind').children('option:selected').val(),
                                $('#addColumnIndex').children('option:selected').val(),
                                $('#addColumnNull:checked').val() != undefined,
                                $('#addColumnDefault').val()
                                );">
                        Add column
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Drop field from table -->
    <div class="modal fade" id="dialogTablesDropField" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    ...

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="tablesColumnDrop(base64encode($('#dialogTablesDropField').data('fieldName')), true);">
                        Drop column
                    </button>
                    <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Drop table -->
    <div class="modal fade" id="dialogTablesDrop" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    ...

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="tablesDrop(base64encode($('#dialogTablesDrop').data('database')), base64encode($('#dialogTablesDrop').data('table')), true);">
                        Drop table
                    </button>
                    <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add row to table -->
    <div class="modal fade" id="dialogViewRowAdd" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    ...

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="tablesViewRowAdd(true);">
                        Add row
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit row from table -->
    <div class="modal fade" id="dialogViewRowEdit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    ...

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="tablesViewRowEdit(
                                base64encode($('#dialogViewRowEdit').data('primaryValue')),
                                false
                                );">
                        Save row
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete row from table -->
    <div class="modal fade" id="dialogViewRowDelete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">

                    ...

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="tablesViewRowDelete(
                                base64encode($('#dialogViewRowDelete').data('primaryValue')),
                                true
                                );">
                        Delete row
                    </button>
                    <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/inc/menuSide.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <?php include_once __DIR__ . '/inc/beginSite.php'; ?>

                <ol class="breadcrumb mb-4 mt-4">
                    <li class="breadcrumb-item active">Databases/Tables</li>
                </ol>

                <div id="databases">
                </div>

                <div id="edit" style="display:none">
                    <div class="text-right mb-4">
                        <button id="buttonEditTable" class="btn btn-secondary" type="button"
                                onclick="
                                    $('#addColumnName').val('');
                                    $('#addColumnKind').val('string').change();
                                    $('#addColumnNull').prop('checked', true);
                                    $('#addColumnDefault').val('');
                                    $('#addColumnIndex').val('').change();
                                    $('#dialogTablesAddField').modal('show').on('shown.bs.modal', function (e) {
                                      $('#addColumnName').focus();
                                    });">Add column
                        </button>
                        <button class="btn btn-secondary" type="button" onclick="tablesEditClose();">Close table
                        </button>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover table-striped" id="editCells">
                            <thead>
                            <tr>
                                <th>Field name</th>
                                <th width="1%" nowrap>Type</th>
                                <th width="1%" nowrap>NULL</th>
                                <th width="1%" nowrap>Default</th>
                                <th width="1%" nowrap>Index</th>
                                <th width="1%" nowrap>Options</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="view" style="display:none">
                    <div class="text-right mb-4">
                        <button id="buttonAddRow" class="btn btn-secondary" type="button"
                                onclick="tablesViewRowAdd(false);">Add row
                        </button>
                        <button class="btn btn-secondary" type="button" onclick="tablesViewClose();">Close table
                        </button>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover table-striped" id="viewCells">
                            <thead>
                            <tr>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div id="viewPagination" class="mb-4">

                    </div>
                </div>

                <?php include_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php include_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
