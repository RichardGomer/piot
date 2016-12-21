<?php

/**
 * PiOT Heating Module API Handler
 */

namespace TempAPI;

use QuickAPI as API;


// Get current service status
class TempHandler implements API\APIHandler
{
    public function __construct()
    {
        
    }
    
    public function handleCall($args)
    {
        $log = new \Temp\TempLog($store = new \mods\JsonStore('templog'));
        
        $out = array();
        
        if(array_key_exists('n', $args))
        {
            $n = (int) $args['n'];
        }
        else
        {
            $n = 3;
        }
        
        foreach($log->getChannels() as $c)
        {
            $readings = $log->getLastReadings($c, $n);
        
            $out[$c] = array();
            $prev = false;
            $prevt = false;
            foreach($readings as $t=>$r)
            {
                // Remove obviously wrong readings (+/- 10C from last)
                if($prev !== false)
                {
                    if($prevt > $t - 600 && abs($prev - $r) > 10)
                        continue;
                }
            
                $out[$c][$t] = number_format($r, 1, '.', '');
                $prevt = $t;
                $prev = $r;
            }
        }
        
        return $out;
    }
}


$API->addOperation(false, array('temp', 'n'), $th = new TempHandler());
$API->addOperation(false, array('temp'), $th);

?>
