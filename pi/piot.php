<?php


/**
 * Pi-OT: A lightweight framework for deploying RasPi-based IOT devices
 *
 * Actually, it's web-of-things, because all the API is HTTPS-based :)
 *
 * This script starts the Pi-OT system
 */


// Lighthttpd should be started for the API automatically...

// Load system libs
require 'lib/gpio.lib.php';
require 'lib/watchdog.php';
require 'lib/mods.lib.php';

// Load module libs
mods\runmods('mods-lib');

// Run startup mods
mods\runmods('mods-startup');

// Set up Watchdog
$wd = new Watchdog();

// Stay alive, patting the watchdog and running the regular modules
while(true){
    $wd->pat();
    
    mods\runmods('mods-regular'); // Run all regular mods
    
    sleep(5);
}


?>
