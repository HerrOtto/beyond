<?php

header('Content-type: text/html; Charset=UTF-8');

require_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin,view')) {
    // Is not admin or viewer
    header('Location: ' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/login.php');
    exit;
}

?>
<html>
<head>
    <title>Users</title>
    <?php require_once __DIR__ . '/inc/head.php'; ?>

    <style>

        .userCell {
            cursor: pointer;
        }

        .rolesCell {
            cursor: pointer;
        }

    </style>
    <script>

        function usersFetch(initialEdit = '') {
            <?php print $beyond->prefix; ?>api.users.fetch({
                //
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (typeof data.fetch === 'object') {
                        $('#userTable > tbody').empty();
                        for (userName in data.fetch) {
                            $('#userTable').append(
                                '<tr>' +
                                '<td class="userCell" value="' + btoa(userName) + '">' + userName + '</td>' +
                                '<td class="rolesCell" value="' + btoa(data.fetch[userName].roles) + '">' + data.fetch[userName].roles + '</td>' +
                                '<td>' +
                                '  <span style="cursor:pointer;" onclick="usersDelete(\'' + btoa(userName) + '\');"><i class="fas fa-trash"></i></span>' +
                                '</td>' +
                                '</tr>'
                            );
                            if ((initialEdit !== '') && (initialEdit == userName)) {
                                usersEdit(userName, data.fetch[userName].roles, '', '', false);
                            }
                        }
                        $('.userCell,.rolesCell').on('click', function() {
                            var tr = $(this).closest('tr');
                            var userName = atob(tr.children('.userCell').attr('value'));
                            var roles = atob(tr.children('.rolesCell').attr('value'));
                            usersEdit(userName, roles, '', '', false);
                        })

                    } else {
                        message('Fetching users failed');
                    }
                }
            });
        }

        function usersAdd(userName, roles, password1, password2) {
            <?php print $beyond->prefix; ?>api.users.add({
                'userName': userName,
                'roles': roles,
                'password1': password1,
                'password2': password2
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.add === true) {
                        $('#dialogUserAdd').modal('hide');
                        usersFetch();
                    } else {
                        message('Adding user failed');
                    }
                }
            });
        }

        function usersEdit(userName, roles, password1, password2, fromModal = false) {

            if (fromModal === false) {
                $('#editUserName').val(userName);
                $('#editRoles').val(roles);
                $('#editPassword1').val('');
                $('#editPassword2').val('');
                $('#dialogUsersEdit').modal('show').on('shown.bs.modal', function() {
                    $('#editRoles').focus();
                });
                return false;
            }
            <?php print $beyond->prefix; ?>api.users.modify({
                'userName': userName,
                'roles': roles,
                'password1': password1,
                'password2': password2
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.modify === true) {
                        $('#dialogUsersEdit').modal('hide');
                        usersFetch();
                    } else {
                        message('User [' + userName + '] modification failed');
                    }
                }
            });
        }

        function usersDelete(userNameBase64, fromModal = false) {
            if (fromModal === false) {
                $('#dialogUsersDelete .modal-body').html('Delete user: <b>' + atob(userNameBase64) + '</b>');
                $('#dialogUsersDelete').data('userName', atob(userNameBase64)).modal('show');
                return false;
            }
            <?php print $beyond->prefix; ?>api.users.delete({
                'userName': atob(userNameBase64)
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error);
                } else {
                    if (data.delete === true) {
                        $('#dialogUsersDelete').modal('hide');
                        usersFetch();
                    } else {
                        message('User [' + atob(userNameBase64) + '] deletion failed');
                    }
                }
            });
        }

        $(document).ready(function () {

            // Fetch users
            usersFetch(
                <?php print json_encode($beyond->variable->get('edit', '')); ?> // Open editor for this user
            );

            // Modal: Add user (On show)
            $('#dialogUserAdd').on('shown.bs.modal', function (e) {
                $('#userName').focus();
            });

        });

    </script>
</head>
<body class="sb-nav-fixed">
<?php require_once __DIR__ . '/inc/begin.php'; ?>
<?php require_once __DIR__ . '/inc/menuTop.php'; ?>
<div id="layoutSidenav">
    <?php require_once __DIR__ . '/inc/menuSide.php'; ?>

    <!-- Create user -->
    <div class="modal fade" id="dialogUserAdd" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form onsubmit="return false;">
                        <div class="form-group">
                            <label class="small mb-1" for="userName">User name</label>
                            <input class="form-control py-4" id="userName" type="text"
                                   placeholder="Enter new user name here"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="roles">Roles (Default roles are "admin" and "view")</label>
                            <input class="form-control py-4" id="roles" type="text"
                                   placeholder="Enter roles here (comma separated)"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="password1">Password</label>
                            <input class="form-control py-4" id="password1" type="password"
                                   placeholder="Enter password here"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="password2">Password confirmation</label>
                            <input class="form-control py-4" id="password2" type="password"
                                   placeholder="Confirmation password here"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="usersAdd($('#userName').val(), $('#roles').val(), $('#password1').val(), $('#password2').val());">
                        Add user
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete user -->
    <div class="modal fade" id="dialogUsersDelete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Delete user: ...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button"
                            onclick="usersDelete(btoa($('#dialogUsersDelete').data('userName')), true);">
                        Delete user
                    </button>
                    <button class="btn btn-success" type="button" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit user -->
    <div class="modal fade" id="dialogUsersEdit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form onsubmit="return false;">
                        <div class="form-group">
                            <label class="small mb-1" for="userName">User name</label>
                            <input class="form-control py-4" id="editUserName" type="text"
                                   placeholder="Enter new user name here" readonly />
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="roles">Roles</label>
                            <input class="form-control py-4" id="editRoles" type="text"
                                   placeholder="Enter roles here (separated by space)"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="password1">Password (Leave empty to keep current password)</label>
                            <input class="form-control py-4" id="editPassword1" type="password"
                                   placeholder="Enter password here"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="password2">Password confirmation</label>
                            <input class="form-control py-4" id="editPassword2" type="password"
                                   placeholder="Confirmation password here"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button"
                            onclick="usersEdit($('#editUserName').val(), $('#editRoles').val(), $('#editPassword1').val(), $('#editPassword2').val(), true);">
                        Save user
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <?php require_once __DIR__ . '/inc/beginSite.php'; ?>

                <ol class="breadcrumb mb-4 mt-4">
                    <li class="breadcrumb-item active">Users</li>
                </ol>

                <div class="text-right mb-4">
                    <button id="buttonAdd" class="btn btn-secondary" type="button" data-toggle="modal"
                            data-target="#dialogUserAdd">Add user
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="userTable">
                        <thead>
                        <tr>
                            <th width="50%">User name</th>
                            <th>Roles</th>
                            <th width="1%" nowrap></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <?php require_once __DIR__ . '/inc/endSite.php'; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
