<?php

/**
 * Pi-OT HTTP API
 *
 * This uses the QUAPI API framework. It basically loads all the files in mods-enabled, which should do whatever to the global API object
 */
 
require '../common.php';
 
include '../lib/gpio.lib.php'; // GPIO library
include '../lib/quapi/api.lib.php';
include 'auth.lib.php';
include '../lib/mods.lib.php';

use QuickAPI as API;

$API = new API\API(array_merge($_GET, $_POST), 'op');

$auth = new PIOT\IPAuth('10.0.0.0/16');
$API->addAuth($auth, array());

mods\runmods('../mods-lib', array('API'=>$API)); // Load global module libs
mods\runmods('../mods-api', array('API'=>$API)); // Load API mods

$API->handle();



?>
