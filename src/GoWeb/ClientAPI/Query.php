<?php

namespace GoWeb\ClientAPI;

abstract class Query
{
    const REQUEST_METHOD_GET    = 'GET';
    const REQUEST_METHOD_POST   = 'POST';
    const REQUEST_METHOD_PUT    = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';

    protected $_requestMethod = self::REQUEST_METHOD_GET;

    protected $_url;

    protected $_query = array();

    protected $_headers = array();

    protected $_responseModel = 'GoWeb\Api\Model';

    protected $_rawResponse;
    
    /**
     *
     * @var booleand cache switcher
     */
    protected $_cache = false;
    
    /**
     *
     * @var int cache interval. If value greater than (30 * 24 * 60 * 60) - value is Unix timestamp
     */
    protected $_cacheExpire = 43200;
    
    /**
     *
     * @var mixed variable, used to store cache results
     */
    private $_cachedValue;

    /**
     *
     * @var \GoWeb\ClientAPI
     */
    protected $_clientAPI;

    public function __construct(\GoWeb\ClientAPI $api)
    {
        $this->_clientAPI = $api;

        $this->addHeader('Accept-Language', $api->getLanguage());

        $this->init();
    }

    /**
     * Object initializer, may be redefined in child classes
     */
    protected function init()
    {

    }

    public function getClientAPI()
    {
        return $this->_clientAPI;
    }

    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;

        return $this;
    }

    public function getHeader($name)
    {
        return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setParam($name, $value)
    {
        $this->_query[$name] = $value;

        return $this;
    }
    
    public function setParams(array $params)
    {
        $this->_query = $params;
        
        return $this;
    }
    
    public function addParams(array $params)
    {
        $this->_query = array_merge($this->_query, $params);
        
        return $this;
    }

    public function getParam($name)
    {
        return isset($this->_query[$name]) ? $this->_query[$name] : null;
    }
    
    public function removeParam($name)
    {
        unset($this->_query[$name]);
        return $this;
    }

    public function toArray()
    {
        return $this->_query;
    }

    public function toJson()
    {
        return json_encode($this->_query);
    }

    public function get()
    {
        $this->_requestMethod = self::REQUEST_METHOD_GET;

        return $this;
    }

    public function insert()
    {
        $this->_requestMethod = self::REQUEST_METHOD_POST;

        return $this;
    }

    public function update()
    {
        $this->_requestMethod = self::REQUEST_METHOD_PUT;

        return $this;
    }

    public function delete()
    {
        $this->_requestMethod = self::REQUEST_METHOD_DELETE;

        return $this;
    }

    public function getRawResponse()
    {
        return $this->_rawResponse;
    }
    
    public function getValidateErrors() 
    {
        if(empty($this->_rawResponse['validate_errors'])) {
            return array();
        }
        
        return $this->_rawResponse['validate_errors'];
    }

    protected function _getCacheKey()
    {
        $key = implode(' ', array(
            $this->_requestMethod,
            $this->_url,
            $this->getRequest()->getQuery(),
            $this->_clientAPI->getLanguage()
        ));
        
        return strlen($key) . md5($key);
    }
    
    public function getRequest()
    {
        // send token
        if($this->_clientAPI->isUserAuthorised()) {
            $this->_headers['X-Auth-Token'] = $this->_clientAPI->getActiveUser()->getToken();
        }

        // create request
        $request = $this->_clientAPI
            ->getConnection()
            ->createRequest
            (
                $this->_requestMethod,
                $this->_url,
                $this->_headers,
                null,
                array(
                    'timeout'         => 5,
                    'connect_timeout' => 2
                )
            );

        // set query params
        $request->getQuery()->replace($this->_query);
        
        return $request;
    }
    
    public function send() 
    {
        if(!$this->isCacheEnabled()) {
            return $this->sendOmittingCache();
        }
        
        if($this->_cachedValue) {
            return $this->_cachedValue;
        }
        
        $cacheKey = $this->_getCacheKey();
        
        // get from cache
        $cachedValue = $this->getCacheAdapter()->get($cacheKey);

        // get from remote server and set to cache
        if($cachedValue) {
            $cachedValue = unserialize($cachedValue);
        }
        else {
            $cachedValue = $this->sendOmittingCache();
            $this->getCacheAdapter()->set(
                $cacheKey, 
                serialize($cachedValue), 
                $this->_cacheExpire
            );
        }
        
        $this->_cachedValue = $cachedValue;
        return $this->_cachedValue;
    }
    
    protected function sendOmittingCache()
    {
        // try to auth if not yet authorised
        if(!$this->_clientAPI->isUserAuthorised()) {
            if(!($this instanceof \GoWeb\ClientAPI\Query\Auth)) {
                // use lazy auth if this query is no Query\Auth
                $this->_clientAPI->auth()->send();
            }
        }
        
        // get response
        try {
            $response = $this->getRequest()->send();
        }
        catch(\Guzzle\Http\Exception\BadResponseException $e) {
            switch($e->getResponse()->getStatusCode())
            {
                case 403:
                    throw new \GoWeb\ClientAPI\Query\Exception\Forbidden('Forbidden to proceed query');

                default:
                    throw new \GoWeb\ClientAPI\Query\Exception\Common('Service return responce code ' . $e->getResponse()->getStatusCode());
            }
        }
        catch(\Exception $e) {
            throw new \GoWeb\ClientAPI\Query\Exception\Common($e->getMessage());
        }

        $this->_rawResponse = $response->json();

        // throw exception if error exists
        if(1 == $this->_rawResponse['error']) {
            $errorMessage = isset($this->_rawResponse['errorMessage'])
                ? $this->_rawResponse['errorMessage'] 
                : null;
            
            throw new \GoWeb\ClientAPI\Query\Exception\Common($errorMessage);
        }

        return new $this->_responseModel($this->_rawResponse);
    }
    
    /**
     * 
     * @return \GoWeb\ICacheAdapter 
     */
    public function getCacheAdapter()
    {
        return $this->getClientAPI()->getCacheAdapter();
    }
    
    public function isCacheEnabled()
    {
        return $this->_cache && $this->getCacheAdapter();
    }
    
    public function enableCache() {
        $this->_cache = true;
        return $this;
    }
    
    public function disableCache() {
        $this->_cache = false;
        return $this;
    }
    
    public function setCacheExpireTime($time) {
        $this->_cacheExpire = (int) $time;
        return $this;
    }
}
