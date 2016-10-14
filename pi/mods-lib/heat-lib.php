<?php

class TimedRelay
{

    public function __construct(GPIO\OutputPin $pin, Schedule $schedule, $offState = 1)
    {
        $this->pin = $pin;
        $this->defaultState = $defaultState;
        
        $this->pin->setValue = $this->defaultState;
    }
    
    public function update()
    {
        $time = time();
        
        $on = $this->schedule->getState() == 'ON';
        
        $tihs->pin->setValue($on ? !$offState : $offState);
    }

}

class Schedule
{
    public function __construct(mods\Store $storage)
    {
        $this->storage = $storage;
        
        if($this->storage->ranges === false)
        {
            $this->storage->ranges = array();
        }
    }
    
    protected function addRange($range)
    {
        $this->storage->lock();
        $ranges = $this->storage->ranges;
        $ranges[$id = uniqid()] = $range;
        $this->storage->ranges = $ranges;
        $this->storage->release();
    }
    
    protected function delRange($id)
    {
        $this->storage->lock();
        $ranges = $this->storage->ranges;
        unset($ranges[$id]);
        $this->storage->ranges = $ranges;
        $this->storage->release();
    }
    
    // Compare two times in hh:mm format
    // -1 => $a < $b
    // 0 => $a = $b
    // 1 => $a > $b
    protected function timeCmp($a, $b)
    {
        $a = explode(':', $a);
        $b = explode(':', $b);
        
        // First compare hours
        if($a[0] > $b[0])
            return 1;
           
        if($a[0] < $b[0])
            return -1;
            
        // Else we have to look at minutes
        if($a[1] < $b[1])
            return -1;
        
        if($a[1] > $b[1])
            return 1;
        
        return 0;
    }
    
    public function addDaily($start, $end, $state, $day=false)
    {
        // If span covers multiple days (eg 20:00 - 05:00) split it into two
        // If day is specified, the second part needs to be the next day!
        
        
        $this->addRange(array('type'=>'daily', 'day'=>$day, 'start'=>$start, 'end'=>$end, 'state'=>$state));
    }
    
    public function addTemporary($duration, $state='ON')
    {
        $this->addRange(array('type'=>'temp', 'start'=>time(), 'end'=>time() + $duration * 60, 'state'=>$state));
    }
    
    // Return the state of the first active range that we find
    // Else return false
    public function getState()
    {
        $ranges = $this->storage->ranges();
        
        foreach($ranges as $rid=>$r)
        {
            if($this->isRangeActive($r, $rid))
            {
                return $r['state'];
            }
        }
        
        return false;
    }
    
    // See if a single range definition is active
    protected function isRangeActive($range, $id)
    {
        // Daily ranges
        if($range['type'] == 'daily')
        {
            $day = $range['day'];
            $start = $range['start'];
            $end = $range['end'];
                    
            if($day == false || $day == date('N'))
            {
                if($this->timeCmp($start, time()) >= 0) // has started
                {
                    if($this->timeCmp($end, time()) < 0) // has not ended
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
            }
            else
            {
                return false;
            }
        }
        // Temporary ranges
        elseif($range['type'] == 'temp')
        {
            if($range['start'] < time()) // Check if range has started
            {
                if($range['end'] <= time()) // Check if range has ended
                {
                    $this->delRange($id); // Remove expired range
                    return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return false;
            } 
        }
    }
}



?>
