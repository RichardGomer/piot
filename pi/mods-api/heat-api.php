<?php

/**
 * PiOT Heating Module API Handler
 */

namespace HeatAPI;

use QuickAPI as API;


// Get current service status
class StatusHandler implements API\APIHandler
{
    public function __construct($pin_num, $pin_on, \Schedule $sched)
    {
        $this->pin = \GPIO\Pin::phys($pin_num, \GPIO\Pin::OUT);
        $this->pin_on = $pin_on;
        $this->sched = $sched;
    }
    
    public function handleCall($args)
    {
        $value = $this->pin->getValue();
        $state = $value == $this->pin_on ? "ON" : "OFF";
        
        $active = $this->sched->getActiveRanges();
        $boosted = false;
        $boosted_until = 0;
        $scheduled = false;
        $scheduled_until = 0;
        foreach($active as $r)
        {
            if($r['type'] == 'temp')
            {
                $boosted = true;
                $boosted_until = max($boosted_until, $r['end']);
            }
            else
            {
                $scheduled = true;
                $scheduled_until = $r['end'];
            }
        }
        
        $ranges = $this->sched->getRanges();
        foreach($ranges as &$r)
        {
            if($this->sched->isRangeActive($r))
            {
                $r['active'] = true;
            }
        }
        
        return array('now'=>date('H:i', time()), 'timezone'=>date_default_timezone_get(), 'status'=>$state, 'scheduled'=>$scheduled, 'scheduled_until'=>($scheduled ? $scheduled_until : ''), 'boosted'=>$boosted, 'boosted_until'=>($boosted ? date('H:i', $boosted_until) : ''), 'ranges'=>$ranges);
    }
}

// Start a new boost for X minutes
class StartBoostHandler implements API\APIHandler
{
    public function __construct(\Schedule $sched, $mins)
    {
        $this->sched= $sched;
        $this->mins = $mins;
    }
    
    public function handleCall($args)
    {
        $this->sched->addTemporary($this->mins);
    
        return array();
    }
}

// Clear all boosts
class ClearBoostHandler implements API\APIHandler
{
    public function __construct(\Schedule $sched, $mins)
    {
        $this->sched= $sched;
    }
    
    public function handleCall($args)
    {
        
    }
}


// Create schedule objects, attached to the persistent storage used by the main thread
$heating = new \Schedule($heatstore = new \mods\JsonStore('heating'));
$hwater = new \Schedule($hwstore = new \mods\JsonStore('hwater'));

// Set up both services - using a loop to make maintenance easier ;)
foreach(
    array('heating'=>array('sched'=>$heating, 'store'=>$heatstore), 
    'water'=>array('sched'=>$hwater, 'store'=>$hwstore) )
    as $service=>$info)
{
    $sched = $info['sched']; // The schedule for the service
    $store = $info['store']; // The state object for the service

    $pin_num = $store->pin;
    $pin_on = $store->pin_on; // The pin value that corresponds to the service being ON
    
    // Status
    $h = new StatusHandler($pin_num, $pin_on, $sched);
    $API->addOperation(false, array($service.'-status'), $h);

    // Boost - 60 mins
    $h = new StartBoostHandler($sched, 60);
    $API->addOperation(false, array($service.'-boost'), $h);

    
}

?>
