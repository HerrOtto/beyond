<script>
    function logout() {
        <?php print $beyond->prefix; ?>api.beyondAuth.logout({
            //
        }, function (error, data) {
            if (error !== false) {
                message('Error: ' + error)
            } else {
                if (data.logoutDone) {
                    location.href = '<?php print $beyond->config->get('base', 'server.baseUrl'); ?>/beyond/login.php';
                } else {
                    message('Logout failed');
                }
            }
        });
    }
</script>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <span class="navbar-brand"><?php print $beyond->config->get('base', 'site.title', ''); ?></span>
    <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i>
    </button>
    <!-- Navbar Search-->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
        <!--
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search"
                           aria-describedby="basic-addon2"/>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                -->
    </form>

    <!-- Navbar-->
    <ul class="navbar-nav ml-auto ml-md-0">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <?php
                if ($beyond->tools->checkRole('admin')) {
                    print '<a class="dropdown-item" href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/users.php?edit=' . urlencode($_SESSION[$beyond->prefix . 'data']['auth']['userName']) . '">Account</a>' . PHP_EOL;
                }
                ?>
                <!--
                <a class="dropdown-item" href="#">Activity Log</a>
                <div class="dropdown-divider"></div>
                -->
                <span class="dropdown-item" style="cursor:pointer;" onclick="logout();">Logout</span>
            </div>
        </li>
    </ul>
</nav>