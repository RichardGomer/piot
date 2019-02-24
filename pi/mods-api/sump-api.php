<?php

/**
 * Sump API handler
 */
 
namespace SumpAPI;

use QuickAPI as API;

class SumpHandler implements API\APIHandler
{
        public function __construct()
        {
        
        }
        
        public function handleCall($args)
        {
                $log = new \Sump\SumpLog();
        
                $out = array();
                
                // Decide how many readings to return from the log
                if(array_key_exists('n', $args))
                {
                    $n = (int) $args['n'];
                }
                else
                {
                    $n = 10;
                }
                
                return array(
                        'pump' => $log->getLastReadings('pump', $n),
                        'depth' => $log->getLastReadings('depth', $n)
                );
        }
}


$API->addOperation(false, array('sump', 'n'), $sh = new SumpHandler());
$API->addOperation(false, array('sump'), $sh);

