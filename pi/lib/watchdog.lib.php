<?php


/**
 * Pure PHP Watchdog Patter
 */

class Watchdog
{
    public function __construct($dev='/dev/watchdog')
    {
        $this->dev = $dev;
        $this->devh = fopen($dev, 'r+');
    }
    
    public function pat()
    {
        fwrite('1', $this->devh); // You can write anything to pat the watchdog
    }
    
    public function disable()
    {
        fwrite('V', $this->devh); // V is a magic character
    }
}


?>
