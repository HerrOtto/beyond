<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">

            <?php
            if ($beyond->tools->checkRole('admin,view')) {
                print '<div class="nav">' . PHP_EOL;
                print '<div class="sb-sidenav-menu-heading ">Project</div>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'files.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/files.php">' . PHP_EOL;
                print '    <div class="sb-nav-link-icon"><i class="fas fa-folder-open"></i></div>' . PHP_EOL;
                print '    Files' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'apis.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/apis.php">' . PHP_EOL;
                print '    <div class="sb-nav-link-icon"><i class="fas fa-scroll"></i></div>' . PHP_EOL;
                print '    APIs' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'tables.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/tables.php">' . PHP_EOL;
                print '    <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>' . PHP_EOL;
                print '    Database/Tables' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'users.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/users.php">' . PHP_EOL;
                print '    <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>' . PHP_EOL;
                print '    Users' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '</div>' . PHP_EOL;
            }
            ?>

            <?php
            if ($beyond->tools->checkRole('admin,view')) {
                print '<div class="nav">' . PHP_EOL;
                print '<div class="sb-sidenav-menu-heading">Plugins</div>' . PHP_EOL;
                // Include plugins
                foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
                    if (file_exists($pluginDir . '/site.php')) {
                        print '<a class="nav-link ' . ((basename($_SERVER["SCRIPT_FILENAME"]) === 'pluginSite.php') && ($beyond->variable->get('name') === basename($pluginDir)) ? 'active' : '') . '"';
                        print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginSite.php?name=' . basename($pluginDir) . '">';
                        print '    <div class="sb-nav-link-icon"><i class="fas fa-puzzle-piece"></i></div>';
                        print '    ' . basename($pluginDir);
                        print '</a>';
                    }
                }
                unset($pluginDir);
                print '</div>' . PHP_EOL;
            }
            ?>

            <?php
            if ($beyond->tools->checkRole('admin,view')) {
                print '<div class="nav">' . PHP_EOL;
                print '<div class="sb-sidenav-menu-heading ">System</div>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'update.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/update.php">' . PHP_EOL;
                print '    <div class="sb-nav-link-icon"><i class="fas fa-sync"></i></div>' . PHP_EOL;
                print '    Update' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'cleanup.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/cleanup.php">' . PHP_EOL;
                print '     <div class="sb-nav-link-icon"><i class="fas fa-brush"></i></div>' . PHP_EOL;
                print '    Cleanup' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '<a class="nav-link ' . (basename($_SERVER["SCRIPT_FILENAME"]) === 'plugins.php' ? 'active' : '') . '"' . PHP_EOL;
                print '   href="' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/plugins.php">' . PHP_EOL;
                print '     <div class="sb-nav-link-icon"><i class="fas fa-puzzle-piece"></i></div>' . PHP_EOL;
                print '    Plugins' . PHP_EOL;
                print '</a>' . PHP_EOL;
                print '</div>' . PHP_EOL;
            }
            ?>

        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?php print $_SESSION[$beyond->prefix . 'data']['auth']['userName']; ?>
        </div>
    </nav>
</div>