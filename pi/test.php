<?php

require 'lib/mods.lib.php';
require 'mods-lib/temp-lib.php';

/*
$p = proc_open("sudo tail -f test.log", array ( 0 => array ( "pipe", "r" ), 1 => array ( "pipe", "w" ), 2 => array ( "pipe", "w" ) ), $pipes);

stream_set_blocking( $pipes[1], 0 );
stream_set_blocking( $pipes[2], 0 );

while(true)
{
    while(($l = fgets($pipes[1])) !== false)
    {
        echo $l;
    }
    sleep(1);
}
*/

$p = new temp\Thread("sudo mods-bin/oregonrcv");
//$p = new temp\Thread("sudo tail -f test.log");

while(true)
{
    $lines = $p->read();
    foreach($lines as $l)
    {
        echo trim($l)."\n";
    }
    
    $lines = $p->error();
    foreach($lines as $l)
    {
        echo trim($l)."\n";
    }
    
    sleep(1);
}
