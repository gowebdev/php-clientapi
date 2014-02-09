<?php

namespace GoWeb\ClientAPI\Adapter\Yii;

class ClientAPICache implements \Guzzle\Cache\CacheAdapterInterface
{
    /**
     *
     * @var CCache
     */
    protected $_cache;
    
    protected $_keyPrefix = 'GoWebClientAPI';
    
    public function __construct()
    {
        $this->_cache = \Yii::app()->cache;
    }
    
    public function save($id, $data, $lifeTime = false, array $options = null)
    {        
        $this->_cache->set($this->_keyPrefix . $id, $data, $lifeTime);
         
        return $this;
    }
    
    public function fetch($id, array $options = null)
    {
        return $this->_cache->get($this->_keyPrefix . $id);
    }
    
    public function delete($id, array $options = null) 
    {
        $this->_cache->delete($this->_keyPrefix . $id);
    }
    
    public function contains($id, array $options = null) 
    {
        return false !== $this->get($this->_keyPrefix . $id);
    }
}