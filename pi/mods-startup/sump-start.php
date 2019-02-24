<?php

$hole_depth = 70; // Depth of hole (from sensor) in cm
$pump_on = 40; // Pump comes on when this level is reached (in cm)
$pump_off = 15; // And goes off when this level is reached (in cm)

// Obviously, pump_off must be lower than pump_on; and there should be a reasonable range
// to avoid flip-flopping, and to ensure water is actually drained (instead of just going
// up and down hose, for instance...)

$MOD['logger'] = new Sump\SumpLog();

$MOD['sensor'] = new Sump\DepthSensor(31, 33, $hole_depth);

$MOD['pump_range'] = array($pump_on, $pump_off);

// The pump is controlled by two relays (to provide double isolation)
// Controlled by pins 16 and 18
$r1 = GPIO\Pin::phys(16, GPIO\Pin::OUT);
$r2 = GPIO\Pin::phys(18, GPIO\Pin::OUT);
$MOD['pump'] = $pump = new Sump\DblRelay($r1, $r2);

$pump->off();
$MOD['PUMP_STATUS'] = false;


