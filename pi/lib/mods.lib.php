<?php

/**
 * Helpers for module management
 */

namespace mods;

function runMods($dir='mods-enabled', $vars=array())
{
    $mods = scandir($dir);
    foreach($mods as $f)
    {
        if(preg_match('/\.php$/i', $f))
        {
            runMod($dir, $f, $vars);
        }
    }
}

function runMod($dir, $file, $vars)
{
    list($modname, $x) = explode('-', $file, 2);
    
    foreach($vars as $n=>$v)
    {
        $$n = $v;
    }
    
    if(!array_key_exists($modname, $GLOBALS['modstate']))
    {
        $GLOBALS['modstate'][$modname] = array();
    }
    
    $MOD = $GLOBALS['modstate'][$modname];
    
    //echo "Run module $modname\n";
    //var_dump($MOD);
    
    include $dir.'/'.$file;
    
    $GLOBALS['modstate'][$modname] = $MOD;
}

$modstate = array();

/**
 * Really simple class to provide shared access to persistent json objects
 */
class JsonStore
{
    private $datafn, $lockfn;

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
        }
         
        $this->datafn = $dir.$name.'.json';
        $this->lockfn = $dir.$name.'.lock';
        
        if(!file_exists($this->datafn))
        {
            $this->clear();
        }
        
        
        //echo "Created module store '$name' at {$this->datafn}\n";
    }
    
    private $mylock = false;
    public function lock()
    {
        while($this->islocked())
        {
            usleep(500000); // Sleep 0.5 seconds and try again
        }
        
        @file_put_contents($this->lockfn, time());
        $this->mylock = true;
    }
    
    public function release()
    {
        $this->mylock = false;
        
        if(file_exists($this->lockfn))
            unlink($this->lockfn);
    }
    
    protected function islocked()
    {
        if(!file_exists($this->lockfn))
            return false;
            
        // Locks expire after 15s
        if(file_get_contents($this->lockfn) < time() - 15)
            return false;
    }
    
    protected function getData()
    {
        return json_decode(file_get_contents($this->datafn), true);
    }
    
    public function clear()
    {
        file_put_contents($this->datafn, '[]');
        chmod($this->datafn, 0777); // So web server can edit!
    }
    
    public function __get($k)
    {
        $data = $this->getData();
        
        if(!array_key_exists($k, $data))
        {
            return false;
        }
        
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
