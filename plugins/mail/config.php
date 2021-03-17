<?php

// Called from: ../../pluginConfig.php

if ($beyond->variable->get('page', '') === 'settings') {
  include __DIR__ . '/mailSettings.php';
} else {
    include __DIR__ . '/mailOverview.php';
}

?>