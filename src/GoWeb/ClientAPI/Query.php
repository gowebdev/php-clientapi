<?php

namespace GoWeb\ClientAPI;

class Query extends \Sokil\Rest\Client\Request
{    
    /**
     * @deprecated do not use factory getter
     * 
     * @return \GoWeb\ClientAPI
     */
    public function getClientAPI()
    {        
        return $this->_factory;
    }

    /**
     * @deprecated do not modify user 
     * 
     * @param type $url
     * @return \GoWeb\ClientAPI\Query
     */
    public function setUrl($url) 
    {
        $this->_url = $url;
        $this->_request->setPath($url);
        
        return $this;
    }

    /**
     * @deprecated use self::setHeader() 
     * 
     * @param type $name
     * @param type $value
     * @return \GoWeb\ClientAPI\Query
     */
    public function addHeader($name, $value)
    {
        $this->setHeader($name, $value);
        
        return $this;
    }

    /**
     * @deprecated use self::setQueryParam();
     * 
     * @param type $name
     * @param type $value
     * @return \GoWeb\ClientAPI\Query
     */
    public function setParam($name, $value)
    {
        $this->setQueryParam($name, $value);
        
        return $this;
    }
    
    /**
     * @deprecated use self::setQueryParams()
     * 
     * @param array $params
     * @return \GoWeb\ClientAPI\Query
     */
    public function setParams(array $params)
    {
        $this->setQueryParams($params);
        return $this;
    }
    
    /**
     * @deprecated use self::addQueryParams()
     * 
     * @param array $params
     * @return \GoWeb\ClientAPI\Query
     */
    public function addParams(array $params)
    {
        $this->addQueryParams($params);
        return $this;
    }

    /**
     * @deprecated use self::getQueryParam()
     * 
     * @param type $name
     * @return type
     */
    public function getParam($key)
    {
        return $this->getQueryParam($key);
    }
    
    /**
     * @deprecated 
     * @param type $name
     * @return \GoWeb\ClientAPI\Query
     */
    public function removeParam($key)
    {
        $this->removeQueryParam($key);
        
        return $this;
    }
    
    /**
     * @deprecated use direct setters
     * 
     * @param type $name
     * @param type $value
     * @return \GoWeb\ClientAPI\Query
     */
    public function setOption($name, $value)
    {
        $this->_request->getParams()->set($name, $value);
        
        return $this;
    }

    /**
     * @deprecated use direct getters
     *  
     * @param type $name
     * @return \GoWeb\ClientAPI\Query
     */
    public function getOption($name)
    {
        $this->_request->getParams()->get($name);
        
        return $this;
    }

    /**
     * @deprecated use self::getQueryParams()
     * 
     * @return type
     */
    public function toArray()
    {
        return $this->getQueryParams();
    }

    /**
     * @deprecated
     * @return \GoWeb\ClientAPI\Query
     * @throws \Exception
     */
    public function get()
    {
        throw new \Exception('Direct modification of request method not allowed');
    }

    /**
     * @deprecated
     * @throws \Exception
     */
    public function insert()
    {
        throw new \Exception('Direct modification of request method not allowed');
    }

    /**
     * @deprecated
     * @throws \Exception
     */
    public function update()
    {
        throw new \Exception('Direct modification of request method not allowed');
    }

    /**
     * @deprecated
     * @throws \Exception
     */
    public function delete()
    {
        throw new \Exception('Direct modification of request method not allowed');
    }
    
    /**
     * @deprecated
     * @throws \Exception
     */
    public function getRequestMethod()
    {
        throw new \Exception('Direct modification and reading of request method not allowed');
    }
    
    /**
     * 
     * @return \GoWeb\Api\Model
     * @throws \GoWeb\ClientAPI\Query\Exception\Forbidden
     * @throws \GoWeb\ClientAPI\Query\Exception\Common
     */
    public function send()
    {        
        // try to auth if not yet authorised
        if(!$this->_factory->isUserAuthorised()) {
            if(!($this instanceof \GoWeb\ClientAPI\Query\Auth)) {
                // use lazy auth if this query is no Query\Auth
                $this->_factory->auth()->send();                
            }
        }
        
        // auth
        if($this->_factory->isUserAuthorised()) {
            $this->_request->addHeader('X-Auth-Token', $this->_factory->getActiveUser()->getToken());
        }
        
        // get response
        try {
            $response = parent::send();
            
            if(1 == $response->error) {
                throw new \Exception($response->errorMessage);
            }
        }
        catch(\Guzzle\Http\Exception\BadResponseException $e) {
            if(!($this instanceof \GoWeb\ClientAPI\Query\Auth)) {
                switch($e->getResponse()->getStatusCode()) {
                    case 401:
                        throw new \GoWeb\ClientAPI\Query\Exception\TokenNotSpecified('Token not specified in headers');

                    case 403:
                        throw new \GoWeb\ClientAPI\Query\Exception\WrongTokenSpecified('Token not found or expired');

                    case 406:
                        throw new \GoWeb\ClientAPI\Query\Exception\OtherDeviceAuthrorized('Token was previously deleted because other device join to same service');

                    default:
                        throw new \GoWeb\ClientAPI\Query\Exception\Common('Service return responce code ' . $e->getResponse()->getStatusCode());
                }
            } else {
                // auth return 403
                throw new \GoWeb\ClientAPI\Query\Exception\Common($e->getMessage());
            }
        }
        catch(\Exception $e) {
            throw new \GoWeb\ClientAPI\Query\Exception\Common($e->getMessage());
        }
        
        return $this->getResponse();
    }
    
    /**
     * @deprecated use self::getResponse()
     * 
     * @return \GoWeb\Api\Model
     * @throws \GoWeb\ClientAPI\Query\Exception\Common
     */
    public function getModel()
    {
        return $this->getResponse();
    }
    
    public function getValidateErrors() 
    {
        $response = $this->getResponse();
        if(!is_array($response->validate_errors)) {
            return array();
        }
        
        return $response->validate_errors;
    }
}
