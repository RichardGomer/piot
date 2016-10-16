<?php


/**
 * Heating controller background process
 *
 * state needs to go into $MOD because that's the only thing that the module loader will persist!
 */

$HEATING_SCHED = new Schedule($heatstore = new mods\JsonStore('heating'));
$HWATER_SCHED = new Schedule($hwstore = new mods\JsonStore('hwater'));

$hwstore->clear();
$heatstore->clear();


$heatpin = GPIO\Pin::phys(18, GPIO\Pin::OUT);
$heatstore->pin = 18; // So the API can check actual status of the service
$heatstore->pin_on = 0;
$HEATING = new TimedRelay($heatpin, $HEATING_SCHED);


$hwpin = GPIO\Pin::phys(16, GPIO\Pin::OUT);
$hwstore->pin = 16; // So the API can check actual status of the service
$hwstore->pin_on = 0;
$HWATER = new TimedRelay($hwpin, $HWATER_SCHED);


// Set basic schedule
$HEATING_SCHED->addDaily('06:30', '07:45', 'ON');
$HEATING_SCHED->addDaily('17:20', '18:00', 'ON');
$HEATING_SCHED->addDaily('20:00', '20:30', 'ON');

$HWATER_SCHED->addDaily('06:15', '08:15', 'ON');
$HWATER_SCHED->addDaily('17:20', '18:00', 'ON');
$HWATER_SCHED->addDaily('20:05', '21:00', 'ON');

// Store state...
$MOD['HWATER_SCHED'] = $HWATER_SCHED;
$MOD['HEATING_SCHED'] = $HWATER_SCHED;

$MOD['HWATER'] = $HWATER;
$MOD['HEATING'] = $HEATING;

// Used by the reg script to keep track of calls
$MOD['HEATING_LAST'] = 0;

?>
