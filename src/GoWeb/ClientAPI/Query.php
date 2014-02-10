<?php

namespace GoWeb\ClientAPI;

class Query
{
    const REQUEST_METHOD_GET    = 'GET';
    const REQUEST_METHOD_POST   = 'POST';
    const REQUEST_METHOD_PUT    = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';

    /**
     * Revalidation request is never sent, and the response is always served from the origin server
     */
    const REVALIDATE_NEVER  = 'never';
    
    /**
     * Always revalidate
     */
    const REVALIDATE_ALWAYS = 'always';
    
    /**
     * Never revalidate and uses the response stored in the cache
     */
    const REVALIDATE_SKIP   = 'skip';
    
    protected $_requestMethod = self::REQUEST_METHOD_GET;

    protected $_url;

    protected $_responseModelClassname = 'GoWeb\Api\Model';

    /**
     *
     * @var \Guzzle\Http\Message\RequestInterface
     */
    private $_request;
    
    private $_rawResponse;
    
    private $_model;
    
    protected $_revalidate = self::REVALIDATE_NEVER;
    
    protected $_cacheExpire = 3600;

    /**
     *
     * @var \GoWeb\ClientAPI
     */
    private $_clientAPI;

    public function __construct(\GoWeb\ClientAPI $api)
    {
        $this->_clientAPI = $api;
        
        // cache
        $this
            ->setRevalidate($this->_revalidate)
            ->setCacheExpireTime($this->_cacheExpire);

        $this->init();
    }

    /**
     * Object initializer, may be redefined in child classes
     */
    protected function init()
    {

    }
    
    /**
     * 
     * @return \GoWeb\ClientAPI
     */
    public function getClientAPI()
    {
        return $this->_clientAPI;
    }

    public function setUrl($url) 
    {
        $this->_url = $url;
        $this->getRequest()->setPath($url);
        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->getRequest()->addHeader($name, $value);

        return $this;
    }

    public function getHeader($name)
    {
        $this->getRequest()->getHeader($name);
    }

    public function getHeaders()
    {
        return $this->getRequest()->getHeaders();
    }

    public function setParam($name, $value)
    {
        $this->getRequest()->getQuery()->set($name, $value);

        return $this;
    }
    
    public function setParams(array $params)
    {
        $this->getRequest()->getQuery()->replace($params);
        
        return $this;
    }
    
    public function addParams(array $params)
    {
        $this->getRequest()->getQuery()->merge($params);
        
        return $this;
    }

    public function getParam($name)
    {
        return $this->getRequest()->getQuery()->get($name);
    }
    
    public function removeParam($name)
    {
        $this->getRequest()->getQuery()->remove($name);
        return $this;
    }

    public function toArray()
    {
        return $this->getRequest()->getQuery()->toArray();
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function get()
    {
        if($this->_request) {
            throw new \Exception('Request method already set');
        }
        
        $this->_requestMethod = self::REQUEST_METHOD_GET;

        return $this;
    }

    public function insert()
    {
        if($this->_request) {
            throw new \Exception('Request method already set');
        }
        
        $this->_requestMethod = self::REQUEST_METHOD_POST;

        return $this;
    }

    public function update()
    {
        if($this->_request) {
            throw new \Exception('Request method already set');
        }
        
        $this->_requestMethod = self::REQUEST_METHOD_PUT;

        return $this;
    }

    public function delete()
    {
        if($this->_request) {
            throw new \Exception('Request method already set');
        }
        
        $this->_requestMethod = self::REQUEST_METHOD_DELETE;

        return $this;
    }
    
    public function alwaysRevalidate() {
        $this->setRevalidate(self::REVALIDATE_ALWAYS);
        return $this;
    }
    
    public function skipRevalidate() {
        $this->setRevalidate(self::REVALIDATE_SKIP);
        return $this;
    }
    
    public function neverRevalidate() {
        $this->setRevalidate(self::REVALIDATE_NEVER);
        return $this;
    }
    
    public function setRevalidate($revalidate)
    {
        $this->_revalidate = $revalidate;
        $this->getRequest()->getParams()->set('cache.revalidate', $revalidate);
        return $this;
    }
    
    public function setCacheExpireTime($time)
    {
        $this->_cacheExpire = (int) $time;
        $this->getRequest()->getParams()->set('cache.override_ttl', $this->_cacheExpire);
        
        return $this;
    }
    
    /**
     * 
     * @return \Guzzle\Http\Message\RequestInterface
     */
    private function getRequest()
    {
        if($this->_request) {
            return $this->_request;
        }

        // create request
        $this->_request = $this->_clientAPI
            ->getConnection()
            ->createRequest(
                $this->_requestMethod,
                $this->_url,
                null,
                null,
                array(
                    'timeout'         => 5,
                    'connect_timeout' => 2
                )
            );
        
        return $this->_request;
    }
    
    public function __toString() {
        return (string) $this->getRequest();
    }
    
    /**
     * 
     * @return \GoWeb\Api\Model
     * @throws \GoWeb\ClientAPI\Query\Exception\Forbidden
     * @throws \GoWeb\ClientAPI\Query\Exception\Common
     */
    public function send()
    {
        $request = $this->getRequest();
        
        // try to auth if not yet authorised
        if(!$this->_clientAPI->isUserAuthorised()) {
            if(!($this instanceof \GoWeb\ClientAPI\Query\Auth)) {
                // use lazy auth if this query is no Query\Auth
                $this->_clientAPI->auth()->send();                
            }
        }
        
        // auth
        if($this->_clientAPI->isUserAuthorised()) {
            $request->addHeader('X-Auth-Token', $this->_clientAPI->getActiveUser()->getToken());
        }
        
        // language
        $request->addHeader('Accept-Language', $this->_clientAPI->getLanguage());
        
        // get response
        try {
            $response = $request->send();
            
            // log request and response
            if($this->getClientAPI()->hasLogger()) {
                $this->getClientAPI()->getLogger()
                    ->debug((string) $request . '<br/></br/>' . (string) $response);
            }
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
        
        $this->_rawResponse = $response;
        
        return $this->getModel();
    }
    
    /**
     * 
     * @return \Guzzle\Http\Message\Response
     */
    public function getRawResponse()
    {
        return $this->_rawResponse;
    }
    
    /**
     * 
     * @return \GoWeb\Api\Model
     * @throws \GoWeb\ClientAPI\Query\Exception\Common
     */
    public function getModel()
    {
        if(!$this->_model) {
            $jsonResponse = $this->_rawResponse->json();
            
            // throw exception if error exists
            if(1 == $jsonResponse['error']) {
                $errorMessage = isset($jsonResponse['errorMessage'])
                    ? $jsonResponse['errorMessage'] 
                    : null;

                throw new \GoWeb\ClientAPI\Query\Exception\Common($errorMessage);
            }

            $this->_model = new $this->_responseModelClassname($jsonResponse);
        }
        
        return $this->_model;
    }
    
    public function getValidateErrors() 
    {
        $jsonResponse = $this->_rawResponse->json();
        
        if(empty($jsonResponse['validate_errors'])) {
            return array();
        }
        
        return $jsonResponse['validate_errors'];
    }
}
