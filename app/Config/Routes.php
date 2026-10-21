<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->post('script-exchange/inbounds', 'MessagesController::saveScriptExchangeMessage');

$routes->post('script-exchange/outbounds', 'ScriptExchangeController::sendMessage');

$routes->post('messages', 'MessagesController::saveSurescriptMessage');
