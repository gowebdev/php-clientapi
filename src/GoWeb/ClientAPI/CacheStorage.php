<?php

namespace GoWeb\ClientAPI;

use \Guzzle\Plugin\Cache\DefaultCacheStorage;

class CacheStorage extends DefaultCacheStorage
{
    protected function getCacheKey(\Guzzle\Http\Message\RequestInterface $request)
    {
        return md5(parent::getCacheKey($request) . $request->getHeader('Accept-Language'));
    }
}