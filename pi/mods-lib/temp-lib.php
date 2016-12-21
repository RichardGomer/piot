<?php

namespace Temp;

class TempLog
{
    protected $store;
    public function __construct(\mods\JSONStore $store)
    {
        $this->store = $store;
    }
    
    public function getChannels()
    {
        return array_keys($this->store->readings);
    }
    
    public function getLastReadings($channel, $n=1)
    {
        $readings = $this->store->readings;
    
        if($n >= count($readings[$channel]))
        {
            return $readings[$channel];
        }
        
        return array_slice($readings[$channel], $n * -1, $n, true);
    }
}

class OregonLogger extends TempLog
{
    private $proc;
    public function __construct(\mods\JSONStore $store)
    {
        parent::__construct($store);
    
        $this->proc = new Thread('sudo ./mods-bin/oregonrcv');
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


/**
 * Based on class from https://gist.github.com/scribu/4736329
 */
class Thread
{
	var $process; // process reference
	var $pipes; // stdio
	var $buffer; // output buffer
	var $output;
	var $error;
	var $timeout;
	var $start_time;
	
	public function __construct($command)
	{
		$this->process = false;
		$this->pipes = array();
	
		$descriptor = array ( 0 => array ( "pipe", "r" ), 1 => array ( "pipe", "w" ), 2 => array ( "pipe", "w" ) );
		
		// Open the resource to execute $command
		$this->process = proc_open( $command, $descriptor, $this->pipes );
		
		// Set STDOUT and STDERR to non-blocking
		stream_set_blocking( $this->pipes[1], 0 );
		stream_set_blocking( $this->pipes[2], 0 );
	}
	
	// Close the process
	public function close()
	{
		$r = proc_close( $this->process );
		$this->process = false;
		return $r;
	}
	
	//Get the status of the current runing process
	function getStatus()
	{
		return proc_get_status( $this->process );
	}
	
	
	// Send a message to the command running
	public function tell( $thought )
	{
		fwrite( $this->pipes[0], $thought );
	}
	
	// Get the command output produced since last read, as an array of lines
	public function read($pipe=1)
	{
		$buffer = array();
		
		while ($r = fgets($this->pipes[$pipe]))
		{
			$buffer[] = $r;
		}
		
		return $buffer;
	}
	
	// What command wrote to STDERR
	function error()
	{
		return $this->read(2);
	}
}


