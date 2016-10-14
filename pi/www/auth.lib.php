<?php

namespace PIOT;

class ipAuth implements APIAuth
{
    protected function cidr_match($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);

        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet))
        { 
            return true;
        }

        return false;
    }
    
    public function __construct($cidr)
    {
        $this->cidr = $cidr;
    }
    
    public function checkCredentials($args, QuickAPI\APIHandler $handler)
    {
        return cidr_match($_SERVER['REMOTE_ADDR'], $this->cidr);
    }
}



?>
