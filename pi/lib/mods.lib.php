<?php

/**
 * Helpers for module management
 */

namespace mods;

function runMods($dir='mods-enabled')
{
    $mods = scandir($dir);
    foreach($mods as $f)
    {
        if(preg_match('/\.php$/i', $f))
        {
            include $f;
        }
    }
}

/**
 * Really simple class to provide shared access to persistent json objects
 */
class JsonStore
{
    public function __construct($name, $dir=false)
    {
        if($dir === false)
        {
            $dir = '/home/pi/piot/store/';
            if(!is_dir($dir))
            {
                if(!mkdir($dir))
                {
                    trigger_error("Cannot create persistent storage at $dir", E_USER_ERROR);
                }
            }
            
            $this->datafn = $dir.'/'.$name.'.json';
            $this->lockfn = $dir.'/'.$name.'.lock';
        }
    }
    
    private $mylock = false;
    public function lock()
    {
        while($this->islocked())
        {
            usleep(500000); // Sleep 0.5 seconds and try again
        }
        
        file_put_contents(time(), $this->lockfn);
        $this->mylock = true;
    }
    
    public function release()
    {
        $this->mylock = false;
        unlink($this->lockfn);
    }
    
    protected function islocked()
    {
        if(!file_exists($this->lockfn))
            return false;
            
        // Locks expire after 15s
        if(file_get_contents($this->lockfn)) < time() - 15)
            return false;
    }
    
    protected function getData()
    {
        return json_decode(file_get_contents($this->datafn));
    }
    
    public function clear()
    {
        file_put_contents($this->datafn, '[]');
    }
    
    public function __get($k)
    {
        $data = $this->getData();
        return $data[$k];
    }
    
    public function __set($k, $v)
    {
        $data = $this->getData();
        $data[$k] = $v;
        file_put_contents($this->datafn, json_encode($data));
    }
}

?>
