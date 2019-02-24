<?php

namespace Temp;

class TempLog extends \PiLog\Log
{

}

class OregonLogger extends TempLog
{
    private $proc;
    public function __construct(\mods\JSONStore $store)
    {
        parent::__construct($store);
    
        $this->proc = new \Thread\Thread('sudo ./mods-bin/oregonrcv');
    }
    
    private $maxreadings = 72 * 24 * 60 / 5; // Max readings per channel (we store a max of 1 per 5 mins, so 14 * 24 * 60 = 14 days)
    private $resolution = 300; // 5 minute resolution
    private $readings = array();
    protected function readBuffer()
    {
        $lines = $this->proc->read();
        
        echo "Get lock on temp store  ";
        $this->store->lock();
        echo " [ OK ]\n";
        $readings = $this->store->readings;
        
        foreach($lines as $l)
        {
            $l = trim($l);
        
            if(preg_match('/([0-9]+): ([0-9]{1,2}\.[0-9]+)/', $l, $matches))
            {
                $channel = $matches[1];
                $temp = $matches[2];
                if(!array_key_exists($channel, $readings)){
                    $readings[$channel] = array();
                }
                
                if(count($readings[$channel]) > $this->maxreadings)
                {
                    unset($readings[$channel][array_keys($readings[$channel])[0]]);
                }
                
                $time = floor(time() / $this->resolution) * $this->resolution;
                $readings[$channel][$time] = $temp;
                echo "-> $time: $channel $temp\n";
            }
        }
        
        $this->store->readings = $readings; // sync with store
        $this->store->release();
        echo "Released lock on temp store\n";
    }
    
    public function log()
    {
        $this->readBuffer();
    }
}



