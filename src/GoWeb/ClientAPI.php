<?php

namespace GoWeb;

use Guzzle\Http\Client;

use \Guzzle\Cache\CacheAdapterInterface;
use \Guzzle\Plugin\Cache\CachePlugin;
use \GoWeb\ClientAPI\CacheStorage;

class ClientAPI extends \Sokil\Rest\Client\Factory
{
    protected $_requestClassNamespace = '\GoWeb\ClientAPI\Query';

    private $_email = '';
    
    private $_password = '';
    
    private $_agent = null;
    
    protected $_curlOptions = array(
        CURLOPT_TIMEOUT_MS          => 15000,
        CURLOPT_CONNECTTIMEOUT_MS   => 5000,
    );
    
    private $_meta;

    public function __construct($options = null)
    {
        if(is_array($options)) {
            // server url
            if(isset($options['apiServerUrl'])) {
                $this->setAPIServerUrl($options['apiServerUrl']);
            }
            // cache
            if(isset($options['cacheAdapter']) && $options['cacheAdapter'] instanceof CacheAdapterInterface) {
                $this->setCacheAdapter($options['cacheAdapter']);
            }
        } else {
            parent::__construct($options);
        }
    }

    /**
     * @deprecated
     * @return string API server URL
     */
    public function getAPIServerUrl()
    {
        return $this->getHost();
    }

    /**
     * @deprecated
     * @param type $host
     */
    public function setAPIServerUrl($host)
    {
        $this->setHost($host);
        return $this;
    }
    
    /**
     * @deprecated
     * 
     * @param string $queryName name of query
     * @return GoWeb\ClientAPI\Query
     * @throws \GoWeb\ClientAPI\Query\Exception
     */
    public function query($queryName)
    {        
        return $this->createRequest($queryName);
    }

    protected function _getCacheKeyGenerator() {
        $that = $this;
        return function(\Guzzle\Http\Message\Request $request) use($that) {
            $prefix = 'CAPI';
            
            if($that->isUserAuthorised()) {
                $prefix .= $that->getActiveUser()->getProfile()->getAgent();
            }
            
            $prefix .= $request->getHeader('Accept-Language');
            
            return $prefix;
        };
    }

    private $_activeUser;
    
    /**
     * 
     * @return \GoWeb\Api\Model\Client
     */
    public function getActiveUser()
    {
        return $this->_activeUser;
    }

    public function isUserAuthorised()
    {
        return (bool) $this->_activeUser;
    }

    public function setActiveUser(\GoWeb\Api\Model\Client $user)
    {
        $this->_activeUser = $user;

        return $this;
    }    
    
    public function setCredentials($email, $password)
    {
        $this->_email       = $email;
        $this->_password    = $password;
        
        return $this;
    }
    
    public function setAgent($agent)
    {
        $this->_agent = $agent;
        return $this;
    }
    
    public function getAgent()
    {
        return $this->_agent;
    }

    public function setDemoCredentials($agent = null)
    {
        $this
            ->setCredentials('', '');
        
        if($agent) {
            $this->setAgent($agent);
        }
        
        return $this;
    }
    
    /**
     * Create Auth request
     *
     * @return \GoWeb\ClientAPI\Auth
     */
    public function auth()
    {
        $authQuery = $this->createRequest('Auth');
        
        if($this->_email && $this->_password) {
            $authQuery->byEmail($this->_email, $this->_password);
        } else {
            $authQuery->demo($this->_agent);
        }
        
        return $authQuery;
    }

    public function logout()
    {
        $this->_activeUser = null;
    }
    
    /**
     * 
     * @return \GoWeb\ClientAPI\Validator
     */
    public function getValidator()
    {
        return new ClientAPI\Validator($this);
    }
    
    public function getMeta()
    {
        if(!$this->_meta) {
            $this->_meta = $this
                ->createRequest('Meta')
                ->send();
        }
        
        return $this->_meta;
    }
}