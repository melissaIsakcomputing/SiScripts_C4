<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->post('script-exchange/inbounds', 'V2\\InboundMessageController::saveScriptExchangeMessage');

$routes->post('script-exchange/outbounds', 'ScriptExchangeController::sendMessage');

$routes->post('messages', 'V2\\InboundMessageController::saveSurescriptMessage');
