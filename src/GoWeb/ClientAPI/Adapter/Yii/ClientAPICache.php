<?php

namespace GoWeb\ClientAPI\Adapter\Yii;

class ClientAPICache implements \GoWeb\ClientAPI\ICacheAdapter
{
    protected $_cache;
    
    protected $_keyPrefix = 'GoWebClientAPI';
    
    public function __construct()
    {
        $this->_cache = \Yii::app()->cache;
    }
    
    public function set($name, $value, $expire = null)
    {
        $expire = $expire ? $expire : 0;
        
        $this->_cache->set($this->_keyPrefix . $name, $value, $expire);
         
        return $this;
    }
    
    public function get($name)
    {
        return $this->_cache->get($this->_keyPrefix . $name);
    }
}