<?php

header('Content-type: text/html; Charset=UTF-8');
include_once __DIR__ . '/inc/init.php';

?>
<html>
<head>
    <title>Login</title>
    <?php include_once __DIR__ . '/inc/head.php'; ?>

    <style>

        #loginResult {
            color: red;
        }

        body {
            background: url(<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/images/login/background.jpg) no-repeat center center fixed;
            background-size: cover;
        }

    </style>

    <script>
        function login() {
            <?php print $beyond->prefix; ?>api.beyondAuth.login({
                'userName': $('#userName').val(),
                'password': $('#password').val()
            }, function (error, data) {
                if (error !== false) {
                    message('Error: ' + error)
                } else {
                    if (data.login === true) {
                        location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/index.php';
                    } else {
                        $('#loginResult').text('Error: ' + data.login);
                        if ($('#userName').val() === '') {
                            $('#userName').focus();
                        } else {
                            $('#password').select();
                            $('#password').focus();
                        }
                    }
                }
            });
        }

        $(document).ready(function () {

            $("#loginDialog").modal({
                backdrop: false,
                keyboard: false
            });
            $("#loginDialog").modal('show');
            $("#loginDialog").on('shown.bs.modal', function() {
                $('#userName').focus();
            });

            // Pressed "Enter" key within password field
            $("#password").keyup(function (e) {
                if (e.keyCode == 13) {
                    login();
                }
            });

        });
    </script>
</head>
<body>
<?php include_once __DIR__ . '/inc/begin.php'; ?>

<!-- Login dialog -->
<div class="modal fade" id="loginDialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <label class="small mb-1" for="userName">Username</label>
                    <input class="form-control py-4" id="userName" type="text"
                           placeholder="Enter username"/>
                </div>
                <div class="form-group">
                    <label class="small mb-1" for="password">Password</label>
                    <input class="form-control py-4" id="password" type="password"
                           placeholder="Enter password"/>
                </div>
                <div id="loginResult"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" onclick="login();">Login</button>
            </div>
        </div>
    </div>
</div>

<div id="layoutAuthentication">
    <div id="layoutAuthentication_content">
        <main>
            <div class="container">


            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/inc/end.php'; ?>
</body>
</html>
