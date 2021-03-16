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
            <?php print $beyond->prefix; ?>api.tables.fetch({
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
                            out += '<button class="btn btn-secondary" type="button" onclick="tablesCreate(\'' + btoa(database) + '\', false);">Create table';
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
                                    out += '<span class="tableItemName" onclick="tablesView(\'' + btoa(database) + '\', \'' + btoa(data.fetch[database][table]) + '\', false);">';
                                } else {
                                    out += '<span class="tableItemName" onclick="tablesView(\'' + btoa(database) + '\', \'' + btoa(data.fetch[database][table]) + '\', true);">';
                                }
                                out += data.fetch[database][table];
                                out += '</span>';

                                out += '<span class="tableItemAction text-nowrap">';

                                if (!internal) {
                                    out += '  <i class="fas fa-pen ml-1" onclick="tablesEdit(\'' + btoa(database) + '\', \'' + btoa(data.fetch[database][table]) + '\', false);"></i>';
                                    out += '  <i class="fas fa-trash ml-1" onclick="tablesDrop(\'' + btoa(database) + '\',\'' + btoa(data.fetch[database][table]) + '\');"></i>';
                                } else {
                                    out += '  <i class="fas fa-eye ml-1" onclick="tablesEdit(\'' + btoa(database) + '\', \'' + btoa(data.fetch[database][table]) + '\', true);"></i>';
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
                $('#dialogTablesDrop .modal-body').html('Drop table <b>' + atob(tableBase64) + '</b> from database <b>' + atob(databaseBase64) + '</b>');
                $('#dialogTablesDrop').data('table', atob(tableBase64)).data('database', atob(databaseBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.tables.drop({
                'database': atob(databaseBase64),
                'table': atob(tableBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.drop === true) {
                        $('#dialogTablesDrop').modal('hide');
                        tablesFetch();
                    } else {
                        message('Drop table [' + atob(tableBase64) + '] from database [' + atob(databaseBase64) + '] failed: ' + data.drop);
                    }
                }
            });
        }

        function tablesCreate(databaseBase64, fromModal = false, tableName, fieldName, kind, index, allowNull, defaultValue) {
            if (fromModal === false) {
                $('#dialogTablesCreateTitle').html('Create table in database <b>' + atob(databaseBase64) + '</b>');
                $('#addTableName').val('');
                $('#addTableColumnName').val('');
                $('#addTableColumnKind').val('string').change();
                $('#addTableColumnNull').prop('checked', true);
                $('#addTableColumnDefault').val('');
                $('#addTableColumnIndex').val('').change();
                $('#dialogTablesCreate').data('database', atob(databaseBase64)).modal('show').on('shown.bs.modal', function (e) {
                    $('#addTableName').focus();
                });
                return false;
            }

            var fields = [];
            fields[fieldName] = {
                'kind': kind,
                'index': index,
                'null': allowNull,
                'default': defaultValue
            };
            <?php print $beyond->prefix; ?>api.tables.create({
                'database': atob(databaseBase64),
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
                        message('Create table [' + tableName + '] in database [' + atob(databaseBase64) + '] failed: ' + data.create);
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

            <?php print $beyond->prefix; ?>api.tables.info({
                'database': atob(databaseBase64),
                'table': atob(tableBase64)
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
                        editDatabase = atob(databaseBase64);
                        editTable = atob(tableBase64);
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
                        message('Fetch table [' + atob(tableBase64) + '] details from database [' + atob(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesEditAddField(fieldName, kind, index, allowNull, defaultValue, fieldCount, internalTable) { // , kind, defaultValue, allowNull, unique, primary
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
                        '<i class="fas fa-trash mt-1" style="cursor:pointer;" onclick="tablesColumnDrop(\'' + btoa(fieldName) + '\');"></i>'
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
            <?php print $beyond->prefix; ?>api.tables.columnAdd(data, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.columnAdd === true) {
                        $('#dialogTablesAddField').modal('hide');
                        tablesEdit(btoa(editDatabase), btoa(editTable), false);
                    } else {
                        message('Add column to table [' + editTable + '] of database [' + editDatabase + '] failed: ' + data.columnAdd);
                    }
                }
            });
        }

        function tablesColumnDrop(fieldNameBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogTablesDropField .modal-body').html('Drop column <b>' + atob(fieldNameBase64) + '</b> of table <b>' + editTable + '</b> in database <b>' + editDatabase + '</b>');
                $('#dialogTablesDropField').data('fieldName', atob(fieldNameBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.tables.columnDrop({
                'database': editDatabase,
                'table': editTable,
                'field': atob(fieldNameBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.columnDrop === true) {
                        $('#dialogTablesDropField').modal('hide');
                        tablesEdit(btoa(editDatabase), btoa(editTable), false);
                    } else {
                        message('Drop column [' + atob(fieldNameBase64) + '] from table [' + editTable + '] of database [' + editDatabase + '] failed: ' + data.columnDrop);
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
        var viewFields = {};
        var viewCurrentPage = 1;
        var viewInternalTable = false;

        function tablesView(databaseBase64, tableBase64, internalTable) {
            $('#editCells tbody').empty();
            viewDatabase = false;
            viewTable = false;
            viewCurrentPage = 1;
            viewPrimaryColumn = '';
            viewPrimaryKind = '';
            viewInternalTable = internalTable;

            <?php print $beyond->prefix; ?>api.tables.info({
                'database': atob(databaseBase64),
                'table': atob(tableBase64)
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
                        viewDatabase = atob(databaseBase64);
                        viewTable = atob(tableBase64);

                        $('#viewCells thead tr').empty();

                        viewColumns = [];
                        viewFields = data.info;
                        var primary = false;
                        var unique = false;
                        for (fieldIndex in data.info) {
                            primary = false;
                            unique = false;
                            if (data.info[fieldIndex].index) {
                                if (data.info[fieldIndex].index === 'primary') {
                                    viewPrimaryColumn = fieldIndex;
                                    viewPrimaryKind = data.info[fieldIndex].kind;
                                    primary = true;
                                } else if (data.info[fieldIndex].index === 'unique') {
                                    unique = true;
                                }
                            }
                            viewColumns.push(fieldIndex);
                            $('#viewCells thead tr').append(
                                '<th nowrap>' + fieldIndex + (primary ? ' <i class="fas fa-key ml-1"></i>' : unique ? ' <i class="fas fa-star ml-1"></i>' : '') + '</th>'
                            );
                        }

                        $('#viewCells thead tr').append(
                            '<th width="1%" nowrap>Options</th>'
                        );

                        tablesViewLoadTable(1);
                    } else {
                        message('Fetch table [' + atob(tableBase64) + '] details from database [' + atob(databaseBase64) + '] failed');
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

            <?php print $beyond->prefix; ?>api.tables.rowCount({
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

                        $('#viewPagination').html('Pages: ' + pageNumbers);
                        tablesViewLoadTableData(loadPage);

                    } else {
                        message('Fetch row count from table [' + atob(tableBase64) + '] of database [' + atob(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewLoadTableData(loadPage) {
            <?php print $beyond->prefix; ?>api.tables.loadData({
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
                            var primaryValue = '';
                            for (fieldName in viewColumns) {
                                if (viewPrimaryColumn === viewColumns[fieldName]) {
                                    primaryValue = data.loadData[rowNo][viewColumns[fieldName]];
                                }
                                cols += '<td>' + data.loadData[rowNo][viewColumns[fieldName]] + '</td>';
                            }
                            var rowData = btoa(JSON.stringify(data.loadData[rowNo]));
                            cols += '<td align=center nowrap>';
                            if ((viewPrimaryColumn !== '') && (viewInternalTable !== true)) {
                                cols += '<i class="fas fa-pen mt-1" style="cursor:pointer;" onclick="console.log(); tablesViewRowEdit(\'' + btoa(primaryValue) + '\', \'' + rowData + '\');"></i>';
                                cols += '<i class="fas fa-trash mt-1 ml-2" style="cursor:pointer;" onclick="tablesViewRowDelete(\'' + btoa(primaryValue) + '\', false);"></i>';
                            }
                            cols += '</td>';
                            $('#viewCells tbody').append('<tr>' + cols + '</tr>');
                        }
                    } else {
                        message('Fetch rows from table [' + atob(tableBase64) + '] of database [' + atob(databaseBase64) + '] failed');
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
                for (field in viewFields) {
                    data +=
                        '<div class="form-group mb-4">' +
                        '<label class="small mb-1" for="addRowField_' + field + '">' + field + '</label>' +
                        '<input class="form-control py-4" id="addRowField_' + field + '" type="text" />' +
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
                fields[field] = $('#addRowField_' + field).val();
            }

            <?php print $beyond->prefix; ?>api.tables.addData({
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
                        message('Add rows to table [' + atob(tableBase64) + '] of database [' + atob(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewRowEdit(primaryValueBase64 = false, dataJsonBase64 = false) {
            if (dataJsonBase64 !== false) {
                dataJson = JSON.parse(atob(dataJsonBase64));

                var data = '';
                for (field in viewFields) {
                    data +=
                        '<div class="form-group mb-4">' +
                        '<label class="small mb-1" for="editRowField_' + field + '">' + (viewPrimaryColumn === field ? '<i class="fas fa-key"></i> ' : '') + field + '</label>' +
                        '<input class="form-control py-4" id="editRowField_' + field + '" type="text" />' +
                        '</div>';
                }
                $('#dialogViewRowEdit .modal-body').html(
                    '<div class="mb-4">Modify row with primary key <b>' + atob(primaryValueBase64) + '</b> from table <b>' + viewTable + '</b> in database <b>' + viewDatabase + '</b></div>' +
                    data
                );
                $('#dialogViewRowEdit').data('primaryValue', atob(primaryValueBase64)).modal('show');

                for (field in viewFields) {
                    $('#editRowField_' + field).val(dataJson[field]);
                }

                return false;
            }

            fields = {};
            for (field in viewFields) {
                fields[field] = $('#editRowField_' + field).val();
            }

            <?php print $beyond->prefix; ?>api.tables.modifyData({
                'database': viewDatabase,
                'table': viewTable,
                'fields': fields,
                'primary': viewPrimaryColumn,
                'kind': viewPrimaryKind,
                'value': atob(primaryValueBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.modifyData !== false) {
                        $('#dialogViewRowEdit').modal('hide');
                        tablesViewLoadTable(viewCurrentPage);
                    } else {
                        message('Modify rows with primary key [' + atob(primaryValueBase64) + '] from table [' + atob(tableBase64) + '] of database [' + atob(databaseBase64) + '] failed');
                    }
                }
            });
        }

        function tablesViewRowDelete(primaryValueBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogViewRowDelete .modal-body').html('<div class="mb-4">Delete row with primary key <b>' + atob(primaryValueBase64) + '</b> from table <b>' + viewTable + '</b> in database <b>' + viewDatabase + '</b></div>');
                $('#dialogViewRowDelete').data('primaryValue', atob(primaryValueBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.tables.deleteData({
                'database': viewDatabase,
                'table': viewTable,
                'primary': viewPrimaryColumn,
                'value': atob(primaryValueBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.deleteData !== false) {
                        $('#dialogViewRowDelete').modal('hide');
                        tablesViewLoadTable(viewCurrentPage);
                    } else {
                        message('Add rows to table [' + atob(tableBase64) + '] of database [' + atob(databaseBase64) + '] failed');
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

                    <div class="form-group">
                        <label class="small mb-1" for="addTableColumnIndex">Index</label>
                        <select class="form-control" id="addTableColumnIndex">
                            <option val="primary">primary</option>
                            <option val="unique">unique</option>
                            <option selected></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="tablesCreate(
                                btoa($('#dialogTablesCreate').data('database')),
                                true,
                                $('#addTableName').val(),
                                $('#addTableColumnName').val(),
                                $('#addTableColumnKind:selected').val(),
                                $('#addTableColumnIndex:selected').val(),
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

                    <div class="form-group">
                        <label class="small mb-1" for="addColumnIndex">Index</label>
                        <select class="form-control" id="addColumnIndex">
                            <option val="primary">primary</option>
                            <option val="unique">unique</option>
                            <option selected></option>
                        </select>
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
                            onclick="tablesColumnDrop(btoa($('#dialogTablesDropField').data('fieldName')), true);">
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
                            onclick="tablesDrop(btoa($('#dialogTablesDrop').data('database')), btoa($('#dialogTablesDrop').data('table')), true);">
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
                                btoa($('#dialogViewRowEdit').data('primaryValue')),
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
                                btoa($('#dialogViewRowDelete').data('primaryValue')),
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
