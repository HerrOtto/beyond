<?php

// Called from: ../../pluginConfig.php

$shopMenu = '';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginConfig.php?name=shop&page=items\';">Items</button>';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginConfig.php?name=shop&page=coupons\';">Coupons</button>';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginConfig.php?name=shop&page=shipping\';">Shipping</button>';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginConfig.php?name=shop&page=database\';">Database</button>';

if ($beyond->variable->get('page', '') === 'database') {
    include __DIR__ . '/configDatabase.php';
} else if ($beyond->variable->get('page', '') === 'coupons') {
    include __DIR__ . '/configCoupons.php';
} else if ($beyond->variable->get('page', '') === 'shipping') {
    include __DIR__ . '/configShipping.php';
} else {
    include __DIR__ . '/configItems.php';
}

?>