<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading ">Project</div>
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'files.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/files.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-folder-open"></i></div>
                    Files
                </a>
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'apis.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/apis.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-scroll"></i></div>
                    APIs
                </a>
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'tables.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/tables.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
                    Database/Tables
                </a>
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'users.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/users.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                    Users
                </a>

            </div>

            <div class="nav">
                <div class="sb-sidenav-menu-heading">Plugins</div>
                <?php
                // Include plugins
                foreach (glob(__DIR__ . '/../plugins/*') as $pluginDir) {
                    if (file_exists($pluginDir . '/config.php')) {
                        print '<a class="nav-link ' . ((basename($_SERVER["SCRIPT_FILENAME"]) === 'pluginConfig.php') && ($variable->get('name') === basename($pluginDir)) ? 'active' : '') . '"';
                        print '   href="' . $config->get('base', 'server.baseUrl') . '/beyond/pluginConfig.php?name=' . basename($pluginDir) . '">';
                        print '    <div class="sb-nav-link-icon"><i class="fas fa-puzzle-piece"></i></div>';
                        print '    ' . basename($pluginDir);
                        print '</a>';
                    }
                }
                ?>
            </div>

            <div class="nav">
                <div class="sb-sidenav-menu-heading">System</div>
                <!--
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'config.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/config.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                    Configuration
                </a>
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'update.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/update.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-sync"></i></div>
                    Update
                </a>
                -->
                <a class="nav-link <?php print basename($_SERVER["SCRIPT_FILENAME"]) === 'plugins.php' ? 'active' : ''; ?>"
                   href="<?php print $config->get('base', 'server.baseUrl') . '/beyond/plugins.php' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-puzzle-piece"></i></div>
                    Plugins
                </a>
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?php print $_SESSION[$prefix . 'data']['auth']['userName']; ?>
        </div>
    </nav>
</div>