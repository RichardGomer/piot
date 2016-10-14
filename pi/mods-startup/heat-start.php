<?php


/**
 * Heating controller background process
 */

$HEATING_SCHED = new Schedule('heating');
$HEATING = new TimedRelay(Pin::phys(18, Pin::OUT), $HEATING_SCHED);

$HWATER_SCHED = new Schedule('hwater');
$HWATER = new TimedRelay(Pin::phys(18, Pin::OUT), $HWATER_SCHED);

$HWATER_SCHED->clear();
$HEATING_SCHED->clear();

// Set basic schedule
$HEATING_SCHED->addDaily('06:30', '07:45', 'ON');
$HEATING_SCHED->addDaily('17:20', '20:20', 'ON');

$HW_SCHED->addDaily('06:15', '08:15', 'ON');
$HW_SCHED->addDaily('16:30', '18:30', 'ON');
$HW_SCHED->addDaily('19:30', '20:30', 'ON');

?>
