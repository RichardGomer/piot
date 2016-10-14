<?php

/**
 * Heating controller, regular update
 *
 * Activates/deactivates heating relays as appropriate
 */
 
if($HEATING_LAST > time() - 30) // Only run every 30s
    return;

$HEATING_LAST = time();

$HOT_WATER->update();
$HEATING->update();
