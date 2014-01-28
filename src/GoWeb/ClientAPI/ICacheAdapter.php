<?php

namespace GoWeb\ClientAPI;

interface ICacheAdapter 
{
    public function set($name, $value, $expire = null);
    
    public function get($name);
}