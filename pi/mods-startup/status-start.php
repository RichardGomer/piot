<?php

$MOD['store'] = $store = new mods\JsonStore('piotstatus');

$store->proc = getmypid();
$store->started = time();
$store->modules = mods\enabledMods();

