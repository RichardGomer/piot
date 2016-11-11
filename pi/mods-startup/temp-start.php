<?php


/*
 * Temp logger setup
 */
 
 $store = new mods\JsonStore('templog');
 $MOD['logger'] = new Temp\OregonLogger($store);
 
 
