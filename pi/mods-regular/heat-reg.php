<?php

/**
 * Heating controller, regular update
 *
 * Activates/deactivates heating relays as appropriate
 */
 
if($MOD['HEATING_LAST'] > time() - 30) // Only run every 30s
    return;

echo "Check heat status ".date("H:i")."\n";

$MOD['HEATING_LAST'] = time();

echo "WATER: ";
$MOD['HWATER']->update();

echo "HEATING: ";
$MOD['HEATING']->update();
