<?php

namespace GoWeb;

use Guzzle\Http\Client;

use \Guzzle\Cache\CacheAdapterInterface;
use \Guzzle\Plugin\Cache\CachePlugin;
use \GoWeb\ClientAPI\CacheStorage;

class ClientAPI extends \Sokil\Rest\Client\Factory
{
    protected $_requestClassNamespace = '\GoWeb\ClientAPI\Request';

    private $_email = '';
    
    private $_password = '';
    
    private $_agent = null;
    
    protected $_curlOptions = array(
        CURLOPT_TIMEOUT_MS          => 15000,
        CURLOPT_CONNECTTIMEOUT_MS   => 5000,
    );
    
    private $_meta;

    private $_services;

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
            parent::__construct();
        } else {
            parent::__construct($options);
        }
    }
    
    protected function behaviors()
    {
        return array(
            'deprecatedRequest' => new \GoWeb\ClientAPI\RequestBehavior,
        );
    }

    public function init()
    {
        // auth
        $this->onBeforeSend(function($event) {
            
            $request = $event['request'];
            
            // try to auth if not yet authorised
            if(!$this->isUserAuthorised()) {
                if(!($request instanceof \GoWeb\ClientAPI\Request\Auth)) {
                    // use lazy auth if this query is no Query\Auth
                    $this->auth()->send();                
                }
            }

            // auth
            if($this->isUserAuthorised()) {
                $request->setHeader('X-Auth-Token', $this->getActiveUser()->getToken());
            }
        });
        
        // on send
        $this->onParseResponse(function($event) {
            if(1 == $event['response']->error) {
                throw new \GoWeb\ClientAPI\Request\Exception\Common($event['response']->errorMessage);
            }
        });
        
        // on error
        $this->onError(function($event) {            
            if($event['request'] instanceof \GoWeb\ClientAPI\Request\Auth) {
                // auth return 403
                throw new \GoWeb\ClientAPI\Request\Exception\Common($e->getMessage());
            }
            
            switch($event['response']->getStatusCode()) {
                case 401:
                    throw new \GoWeb\ClientAPI\Request\Exception\TokenNotSpecified('Token not specified in headers');

                case 403:
                    throw new \GoWeb\ClientAPI\Request\Exception\WrongTokenSpecified('Token not found or expired');

                case 406:
                    throw new \GoWeb\ClientAPI\Request\Exception\OtherDeviceAuthrorized('Token was previously deleted because other device join to same service');

                default:
                    throw new \GoWeb\ClientAPI\Request\Exception\Common('Service return responce code ' . $event['response']->getStatusCode());
            }
        });
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
     * @throws \GoWeb\ClientAPI\Request\Exception
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
            
            if($this->_meta->get('error')) {
                $message = $this->_meta->get('errorMessage');
                throw new \Exception($message ? $message : 'Error fetching meta');
            }
        }
        
        return $this->_meta;
    }

    public function getServices()
    {
        if (!$this->_services) {
            $this->_services = $this
                ->createRequest('Services')
                ->send();

            if ($this->_services->get('error')) {
                $message = $this->_services->get('errorMessage');
                throw new \Exception($message ? $message : 'Error fetching meta');
            }
        }

       return $this->_services;
    }
}