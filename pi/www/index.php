<?php

/**
 * Pi-OT HTTP API
 *
 * This uses the QUAPI API framework. It basically loads all the files in mods-enabled, which should do whatever to the global API object
 */
 
require '../common.php';
 
include '../lib/quapi/api.lib.php';
include 'auth.lib.php';

// Standard PiOT libs
include '../lib/gpio.lib.php'; // GPIO library
include '../lib/mods.lib.php';
include '../lib/log.lib.php';
require '../lib/thread.lib.php';

use QuickAPI as API;

header('Access-Control-Allow-Origin: *');

header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache"); // HTTP/1.0
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

$API = new API\API(array_merge($_GET, $_POST), 'op');

$auth = new PIOT\IPAuth('10.0.0.0/16');
$API->addAuth($auth, array());

mods\runmods('../mods-lib', array('API'=>$API)); // Load global module libs
mods\runmods('../mods-api', array('API'=>$API)); // Load API mods

class ModListHandler implements API\APIHandler
{
    public function __construct()
    {

    }
    
    public function handleCall($args)
    {
        return mods\enabledMods();
    }
}


$API->addOperation(false, array('mods'), new ModListHandler());

$API->handle();



?>
