<?php

// Called from: ../../pluginSite.php

$shopMenu = '';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginSite.php?name=shop&page=orders\';">Orders</button>';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginSite.php?name=shop&page=payments\';">Payments</button>';
$shopMenu .= '<button class="btn btn-secondary mr-1" type="button" onclick="location.href=\'' . $beyond->config->get('base', 'server.baseUrl') . '/beyond/pluginSite.php?name=shop&page=shipping\';">Shipping</button>';

if ($beyond->variable->get('page', '') === 'payments') {
    include __DIR__ . '/siteOrders.php';
} else if ($beyond->variable->get('page', '') === 'shipping') {
        include __DIR__ . '/siteOrders.php';
} else {
    include __DIR__ . '/siteOrders.php';
}

?>