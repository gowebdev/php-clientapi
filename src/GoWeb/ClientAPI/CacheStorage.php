<?php

namespace GoWeb\ClientAPI;

use \Guzzle\Plugin\Cache\DefaultCacheStorage;

class CacheStorage extends DefaultCacheStorage
{
    protected $_clientAPI;
    
    public function setClientAPI(\GoWeb\ClientAPI $clientAPI)
    {
        $this->_clientAPI = $clientAPI;
        return $this;
    }
    
    protected function getCacheKey(\Guzzle\Http\Message\RequestInterface $request)
    {
        $key = parent::getCacheKey($request) . $request->getHeader('Accept-Language');
        
        if($this->_clientAPI && $this->_clientAPI->isUserAuthorised()) {
            $key .= $this->_clientAPI->getActiveUser()->getProfile()->getAgent();
        }
        
        return md5($key);
    }
}