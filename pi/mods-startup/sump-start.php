<?php

$hole_depth = 64; // Depth of hole (from sensor) in cm
$pump_on = 40; // Pump comes on when this level is reached (in cm)
$pump_off = 15; // And goes off when this level is reached (in cm)

$min_interval = 3600; // Minimum amount of time (s) between the start of pumping episodes

// Obviously, pump_off must be lower than pump_on; and there should be a reasonable range
// to avoid flip-flopping, and to ensure water is actually drained (instead of just going
// up and down hose, for instance...)

$log = $MOD['logger'] = new Sump\SumpLog();

$MOD['sensor'] = new Sump\DepthSensor(31, 33, $hole_depth);

$MOD['pump_range'] = array($pump_on, $pump_off);

$MOD['min_interval'] = $min_interval;

$MOD['last_on'] = 0;

$store = new \mods\JsonStore('sumpinfo');
$store->on_level = $pump_on;
$store->off_level = $pump_off;
$store->hole_depth = $hole_depth;

// The pump is controlled by two relays (to provide double isolation)
// Controlled by pins 16 and 18
$r1 = GPIO\Pin::phys(16, GPIO\Pin::OUT);
$r2 = GPIO\Pin::phys(18, GPIO\Pin::OUT);
$MOD['pump'] = $pump = new Sump\DblRelay($r1, $r2);

$pump->off();
$MOD['PUMP_STATUS'] = false;


