<?php

namespace GoWeb;

class ClientAPIFactory
{
    private $_apiList = array();
    
    public function get($host)
    {
        if(!isset($this->_apiList[$host])) {
            $this->_apiList[$host] = new ClientAPI($host);
        }
        
        return $this->_apiList[$host];
    }
}