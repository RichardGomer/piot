<?php

/**
 * Pi-OT HTTP API
 *
 * This uses the QUAPI API framework. It basically loads all the files in mods-enabled, which should do whatever to the global API object
 */
 
include '../lib/gpio.lib.php'; // GPIO library
include '../lib/quapi/api.lib.php';
include 'auth.lib.php';
include '../lib/mods/lib.php';

use QuickAPI as API;

$API = new API\API(array_merge($_GET, $_POST), 'op');

$auth = new PIOT\ipAuth('10.0.0.1/16');
$API->addAuth($auth);

mods\runmods('../mods-lib'); // Load global module libs
mods\runmods('../mods-api'); // Load API mods

$API->handle();



?>
