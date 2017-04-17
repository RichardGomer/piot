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
$HEATING_SCHED->addDaily('07:00', '09:00', 'ON');
$HEATING_SCHED->addDaily('17:30', '19:30', 'ON');
$HEATING_SCHED->addDaily('21:30', '22:30', 'ON');

$HEATING_SCHED->addDaily('11:00', '13:00', 'ON', array(6,7)); // Come on at lunch on sat/sun

$HWATER_SCHED->addDaily('06:30', '10:30', 'ON');
$HWATER_SCHED->addDaily('17:30', '19:00', 'ON');
$HWATER_SCHED->addDaily('21:30', '22:00', 'ON');

// Store state...
$MOD['HWATER_SCHED'] = $HWATER_SCHED;
$MOD['HEATING_SCHED'] = $HWATER_SCHED;

$MOD['HWATER'] = $HWATER;
$MOD['HEATING'] = $HEATING;

// Used by the reg script to keep track of calls
$MOD['HEATING_LAST'] = 0;

?>
