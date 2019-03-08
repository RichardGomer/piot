<?php

/**
 * Measure and log the current water depth, and operate the pump if necessary
 */

list($pump_on, $pump_off) = $MOD['pump_range'];

$pump = $MOD['pump'];
$sensor = $MOD['sensor'];
$log = $MOD['logger'];

echo "Measuring depth...\n";
$depth = $sensor->measure();

if($depth === false)
{
        echo " XX Depth measurement failed\n";
}
else
{
        echo " :: ".$depth."cm\n";
}        


// Log depth, higher resolution (10 secs) when pumping, otherwise every 2 minutes
$log->store('depth', $depth, $MOD['PUMP_STATUS'] == true ? 10 : 120);



if($depth > $pump_on && $MOD['PUMP_STATUS'] != true && time() - $MOD['last_on'] > $MOD['min_interval']) {
        echo "BEGIN PUMPING\n";
        $MOD['PUMP_STATUS'] = true;
        $log->store('pump', 'ON', 1);
        $pump->on();
}

if($depth < $pump_off && $MOD['PUMP_STATUS'] != false) {
        echo "STOP PUMPING\n";
        $MOD['PUMP_STATUS'] = false;
        $log->store('pump', 'OFF', 1);
        $pump->off();
}


