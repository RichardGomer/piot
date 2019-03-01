<?php

/**
 * PiOT Heating Module API Handler
 */

namespace StatusAPI;

use QuickAPI as API;


// Get current service status
class StatusHandler implements API\APIHandler
{
    public function __construct()
    {
        $this->store = new \mods\JsonStore('piotstatus');
    }
    
    public function handleCall($args)
    {
        $uptime = trim(`uptime`);
        $kernel = trim(`uname -r`);
        $host = trim(`hostname`);
        $modules = $this->store->modules;
        
        $daemonproc = $this->store->proc;
        $daemonalive = $this->store->watchdog > time() - 60 && $daemonproc && file_exists('/proc/'.$daemonproc);
    
        preg_match('/[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2} up ([a-z0-9\s]+), .* load average: (.*)$/i', $uptime, $upinfo);
    
        return array(
            'uptime'=>$upinfo[1],
            'load'=>$upinfo[2],
            'cpu_temp'=>$cput,
            'kernel'=>$kernel,
            'host'=>$host,
            'modules'=>$modules,
            'daemonalive'=>$daemonalive,
            'daemonproc'=>$daemonproc
        );
    }
}


$API->addOperation(false, array('status'), new StatusHandler());

?>
