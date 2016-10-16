<?php


/**
 * Pi-OT: A lightweight framework for deploying RasPi-based IOT devices
 *
 * Actually, it's web-of-things, because all the API is HTTPS-based :)
 *
 * This script starts the Pi-OT system
 */


// Lighthttpd should be started for the API automatically...

require 'common.php';

echo "#### PIOT ####\n\nInterfaces\n";
echo passthru('/sbin/ifconfig |grep -B1 "inet addr" |awk \'{ if ( $1 == "inet" ) { print $2 } else if ( $2 == "Link" ) { printf "   %s:" ,$1 } }\' |awk -F: \'{ print $1 ": " $3 }\'');

echo "\n\nInitialising...\n";

// Load system libs
require 'lib/gpio.lib.php';
require 'lib/watchdog.lib.php';
require 'lib/mods.lib.php';

// Load module libs
mods\runmods('mods-lib');

// Run startup mods
mods\runmods('mods-startup');

// Set up Watchdog
//$wd = new Watchdog();

echo "\nRunning...\n";

// Stay alive, patting the watchdog and running the regular modules
while(true){
    //$wd->pat();
    
    mods\runmods('mods-regular'); // Run all regular mods
    
    sleep(5);
}


?>
