<?php

/**
 * Helpers for module management
 */

namespace mods;

// Get an array of enabled module names
function enabledMods()
{
    static $mods = false;
    
    if($mods !== false)
        return $mods;

    $mods = array();

    // Load the list from file, the first time it's requested
    $mcfg = dirname(dirname(__FILE__)).'/mods.conf';
    if(!file_exists($mcfg))
    {
        echo "Cannot get list of enabled modules";
        exit;
    }
    
    $fc = file_get_contents($mcfg);
    
    $fc = explode("\n", $fc);
    foreach($fc as $line)
    {
        @list($content, $comment) = explode('#', $line, 2);
        
        $content = trim($content);
        
        if(strlen($content) > 1)
        {
            $mods[] = $content;
        }
    }
    
    return $mods;
}

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
    
    if(!in_array($modname, enabledMods()))
    {
        return;
    }
    
    foreach($vars as $n=>$v)
    {
        $$n = $v;
    }
    
    if(!array_key_exists($modname, $GLOBALS['modstate']))
    {
        $GLOBALS['modstate'][$modname] = array();
    }
    
    $MOD = $GLOBALS['modstate'][$modname];
    
    //echo "Run $modname\n";
    //var_dump($MOD);
    
    include $dir.'/'.$file;
    
    $GLOBALS['modstate'][$modname] = $MOD;
}

$modstate = array();

/**
 * Really simple class to provide shared access to persistent json objects
 */
class JsonReader
{
    protected $datafn, $datalockfn, $datalockfh;

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
    }
    
    protected function getData()
    {
        $data = json_decode(file_get_contents($this->datafn), true);
        
        return is_array($data) ? $data : array();
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
}
 
class JsonStore extends JsonReader
{
    public function __construct($name, $dir=false)
    { 
        parent::__construct($name, $dir);
    
        // Create a lock file so we can recreate the actual file without destroying locks etc.
        $this->datalockfn = $this->datafn.'.lock';
        
        $this->datalockfh = fopen($this->datalockfn, 'w+');
        @chmod($this->datalockfn, 0777); // So web server can edit!

        if(!file_exists($this->datafn))
        {
            $this->clear();
        }
    }
    
    private $locked = false;
    public function lock()
    {
        if($this->locked)
            return;
    
        flock($this->datalockfh, LOCK_EX);
        $this->locked = true;
    }
    
    public function release()
    {
        if(!$this->locked)
            return;
    
        flock($this->datalockfh, LOCK_UN);
        $this->locked = false;
    }
    
    public function isLocked()
    {
        return $this->locked;
    }
    
    public function clear()
    {
        file_put_contents($this->datafn, '[]');
        chmod($this->datafn, 0777); // So web server can edit!
    }
    
    public function __set($k, $v)
    {
        if(!($locked = $this->isLocked()))
            $this->lock();
        
        $data = $this->getData();
        $data[$k] = $v;
        file_put_contents($this->datafn, json_encode($data));
        
        if(!$locked)
            $this->release();
    }
}

?>
