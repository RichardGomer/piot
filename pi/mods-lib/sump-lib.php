<?php

/**
 * The SUMP module is for measuring water depth in a draniage sump, and controlling a pump
 */
 
 namespace Sump;
 
 
 class SumpLog extends \PiLog\Log
 {
        public function __construct()
        {
                parent::__construct($store = new \mods\JsonStore('sumplog'), 10);
        }
 }
 
 /**
  * Control TWO relays as one; used to provide double-isolation of the pump
  */
 class DblRelay
 {
        public function __construct(\GPIO\OutputPin $a, \GPIO\OutputPin $b, $reverse=true)
        {
                $this->reverse = $reverse;
                $this->a = $a;
                $this->b = $b;
        }
        
        public function on()
        {
                $this->set(true);
        }
        
        public function off()
        {
                $this->set(false);
        }
        
        public function set($on)
        {
                if($this->reverse)
                        $on = !$on;
                        
                $this->a->setValue($on);
                $this->b->setValue($on);
        }
 
 }
 
 
 /**
  * Measure depth using something like an HC-SR04 sensor
  * triggerPhys / ehoPhys are the physical pin numbers of the trigger and echo pins
  */
 class DepthSensor
 {
        public function __construct($triggerPhys, $echoPhys, $maxDepth)
        {
                $this->pinT = $triggerPhys;
                $this->pinE = $echoPhys;
                $this->maxDepth = $maxDepth;
        }
        
        public function measure()
        {
                $lines = $this->runMeasurements($this->pinT, $this->pinE, 10);
                
                $m = array();
                foreach($lines as $l){
                        if(preg_match('/^\.*Distance: ([0-9]+\.[0-9]+) cm/i', $l, $matches)) {
                                $m[] = $matches[1];
                        }
                }
                
                if(count($m) < 1) {
                        return false;
                }
                
                /*
                $mean = $this->mean($m);
                $sd = $this->stdev($m);
                echo "   Distances: ".implode(',', $m)." n=".count($m)." mean=".$mean." s.d.=".$sd."\n";
                $mean = $this->mean($m);
                echo "   Distances: ".implode(',', $m)." n=".count($m)." mean=".$mean."\n";
                $dist = $mean;
                */
                
                $dist = $this->median($m);
                
                echo "Distance: $dist\n";
                
                // The depth of the water is the maxdepth minus the distance we measured
                return $this->maxDepth - $dist;
        }
        
        protected function runMeasurements($pinT, $pinE, $n) 
        {
                $pinT = (int) $pinT;
                $pinE = (int) $pinE;
                $n = (int) $n;
                $proc = new \Thread\Thread("python ./mods-bin/distance.py $pinT $pinE $n");
                $proc->waitForExit();
                return $proc->read();
        }
        
        protected function stdev($array)
        {
                $mean = $this->mean($array);
        
                $ds = 0;
                foreach($array as $x)
                {
                        $ds += ($x - $mean)^2;
                }
                
                return sqrt($ds / count($array));
        }
        
        protected function median($array)
        {
                sort($array);
                
                $mid = floor(count($array) / 2);
                $keys = array_keys($array);
                
                if(count($array) == 1) {
                        return $array[$keys[0]];
                }
                elseif(count($array) % 2 == 0) {
                        return 0.5 * ($array[$keys[$mid]] + $array[$keys[$mid+1]]);
                }
                else {
                        return $array[$keys[$mid]];
                }
        }
        
        protected function mean($array)
        {
                return array_sum($array) / count($array);
        }
 }
 

