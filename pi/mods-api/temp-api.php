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
        
        //var_dump($store->readings);
        
        foreach($log->getChannels() as $c)
        {
            $out[$c] = $log->getLastReadings($c, 10);
        }
        
        return $out;
    }
}


$API->addOperation(false, array('temp'), new TempHandler());

?>
