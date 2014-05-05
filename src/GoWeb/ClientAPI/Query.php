<?php

namespace GoWeb\ClientAPI;

class Query
{
    const REQUEST_METHOD_GET    = 'GET';
    const REQUEST_METHOD_POST   = 'POST';
    const REQUEST_METHOD_PUT    = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';
    
    protected $_requestMethod = self::REQUEST_METHOD_GET;

    protected $_url;

    protected $_responseModelClassname = 'GoWeb\Api\Model';
    
    private $_headers = array();
    
    protected $_query = array();
    
    private $_requestOptions = array();

    /**
     *
     * @var \Guzzle\Http\Message\RequestInterface
     */
    private $_request;
    
    private $_rawResponse;
    
    private $_model;
    
    /**
     *
     * @var int default cache expire time
     */
    protected $_cacheExpire = 3600;

    /**
     *
     * @var \GoWeb\ClientAPI
     */
    private $_clientAPI;

    public function __construct(\GoWeb\ClientAPI $api)
    {
        $this->_clientAPI = $api;

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
        
        if($this->_request) {
            $this->_request->setPath($url);
        }
        
        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        
        if($this->_request) {
            $this->_request->addHeader($name, $value);
        }
        
        return $this;
    }

    public function getHeader($name)
    {
        if($this->_request) {
            return $this->_request->getHeader($name);
        }
        else {
            return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
        }
    }

    public function getHeaders()
    {
        return $this->_request ? $this->_request->getHeaders() : $this->_headers;
    }

    public function setParam($name, $value)
    {
        // modify
        $arraySelector = explode('.', $name);
        $chunksNum = count($arraySelector);
        
        // optimize one-level selector search
        if(1 == $chunksNum) {
            $this->_query[$name] = $value;
            return $this;
        }
        
        // selector is nested
        $section = &$this->_query;

        for($i = 0; $i < $chunksNum - 1; $i++) {

            $field = $arraySelector[$i];

            if(!isset($section[$field])) {
                $section[$field] = array();
            }
            elseif(!is_array($section[$field])) {
                throw new Exception('Assigning subdocument to scalar value');
            }

            $section = &$section[$field];
        }
        
        // update local field
        $section[$arraySelector[$chunksNum - 1]] = $value;
        
        // add to query
        if($this->_request) {
            $this->_request->getQuery()->set($name, $this->_query[$name]);
        }
        
        return $this;
    }
    
    public function setParams(array $params)
    {
        $this->_query = $params;
        
        if($this->_request) {
            $this->_request->getQuery()->replace($params);
        }
        
        return $this;
    }
    
    public function addParams(array $params)
    {
        $this->_request = array_merge($this->_request , $params);
        
        if($this->_request) {
            $this->_request->getQuery()->merge($params);
        }
        
        return $this;
    }

    public function getParam($name)
    {
        if($this->_request) {
            return $this->getRequest()->getQuery()->get($name);
        }
        else {
            return isset($this->_query[$name]) ? $this->_query[$name] : null;
        }
    }
    
    public function removeParam($name)
    {
        unset($this->_query[$name]);
        
        if($this->_request) {
            $this->_request->getQuery()->remove($name);
        }
        
        return $this;
    }
    
    public function setOption($name, $value)
    {
        $this->_requestOptions[$name] = $value;
        
        if($this->_request) {
            $this->_request->getParams()->set($name, $value);
        }
        
        return $this;
    }
    
    public function getOption($name)
    {
        if($this->_request) {
            $this->_request->getParams()->get($name);
        }
        else {
            return isset($this->_requestOptions[$name]) ? $this->_requestOptions[$name] : null;
        }
        
        return $this;
    }

    public function toArray()
    {
        if($this->_request) {
            return $this->_request->getQuery()->toArray();
        }
        else {
            return $this->_query;
        }
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function get()
    {
        $this->_request = null;        
        $this->_requestMethod = self::REQUEST_METHOD_GET;

        return $this;
    }

    public function insert()
    {
        $this->_request = null;  
        $this->_requestMethod = self::REQUEST_METHOD_POST;

        return $this;
    }

    public function update()
    {
        $this->_request = null;  
        $this->_requestMethod = self::REQUEST_METHOD_PUT;

        return $this;
    }

    public function delete()
    {
        $this->_request = null;  
        $this->_requestMethod = self::REQUEST_METHOD_DELETE;

        return $this;
    }
    
    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }
    
    /**
     * 
     * @return \Guzzle\Http\Message\RequestInterface
     */
    private function getRequest()
    {
        if(!$this->_request) {
            $this->_request = $this->_clientAPI
                ->getConnection()
                ->createRequest(
                    $this->_requestMethod,
                    $this->_url,
                    $this->_headers,
                    null,
                    array(
                        'timeout'         => 5,
                        'connect_timeout' => 2,
                    )
                );
            
            if($this->_query) {
                $this->_request->getQuery()->replace($this->_query);
            }
            
            if($this->_requestOptions) {
                $this->_request->getParams()->replace($this->_requestOptions);
            }
        }

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
        try {
            $jsonResponse = $this->_rawResponse->json();
        } catch (\Guzzle\Common\Exception\RuntimeException $e) {
            return array();
        }
        
        if(empty($jsonResponse['validate_errors'])) {
            return array();
        }
        
        return $jsonResponse['validate_errors'];
    }
}
