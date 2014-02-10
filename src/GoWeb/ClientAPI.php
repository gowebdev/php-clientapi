<?php

namespace GoWeb;

use Guzzle\Http\Client;

use \Guzzle\Cache\CacheAdapterInterface;
use \Guzzle\Plugin\Cache\CachePlugin;
use \Guzzle\Plugin\Cache\DefaultCacheStorage;

class ClientAPI
{
    private $_apiServerUrl;

    private $_email = '';
    
    private $_password = '';
    
    private $_connection;

    private $_language;
    
    private $_logger;

    public function __construct(array $options = null)
    {
        // configure api
        if($options) {
            
            // server url
            if(isset($options['apiServerUrl'])) {
                $this->setAPIServerUrl($options['apiServerUrl']);
            }
            
            // cache
            if(isset($options['cacheAdapter']) && $options['cacheAdapter'] instanceof CacheAdapterInterface) {
                $this->setCacheAdapter($options['cacheAdapter']);
            }
        }
    }

    public function getAPIServerUrl()
    {
        return $this->_apiServerUrl;
    }

    public function setAPIServerUrl($newUrl)
    {
        $this->_apiServerUrl = $newUrl;
    }

    /**
     * @param \Guzzle\Cache\CacheAdapterInterface $adapter
     * @return \GoWeb\ClientAPI
     * @link http://guzzle.readthedocs.org/en/latest/plugins/cache-plugin.html
     */
    public function setCacheAdapter(CacheAdapterInterface $adapter)
    {
        $this->getConnection()->addSubscriber(new CachePlugin(array(
            'storage'   => new DefaultCacheStorage($adapter),
        )));
        
        return $this;
    }
    
    /**
     *
     * @param string $lang lang identifier compatible with Accept-Language header
     */
    public function setLanguage($lang)
    {
        $this->_language = $lang;
    }

    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Get Guzzle RESTful client
     *
     * @return \Guzzle\Http\Client
     */
    public function getConnection()
    {
        if(!$this->_connection) {
            $this->_connection = new Client($this->_apiServerUrl);
        }

        return $this->_connection;
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

    /**
     * 
     * @param string $queryName name of query
     * @return GoWeb\ClientAPI\Query
     * @throws \GoWeb\ClientAPI\Query\Exception
     */
    public function query($queryName)
    {
        // Get query class
        $className = '\\GoWeb\\ClientAPI\\Query\\' . $queryName;
        if(!class_exists($className)) {
            throw new \GoWeb\ClientAPI\Query\Exception('Query class not found');
        }
        
        // Create query
        return new $className($this);
    }
    
    
    public function setCredentials($email, $password)
    {
        $this->_email       = $email;
        $this->_password    = $password;
        
        return $this;
    }
    
    public function setDemoCredentials()
    {
        return $this->setCredentials('', '');
    }
    
    /**
     * Create Auth request
     *
     * @return \GoWeb\ClientAPI\Auth
     */
    public function auth()
    {
        $authQuery = $this->query('Auth');
        
        if(null !== $this->_email && null !== $this->_password) {
            $authQuery->byEmail($this->_email, $this->_password);
        }
        
        echo $authQuery;
        
        return $authQuery;
    }

    public function logout()
    {
        $this->_activeUser = null;
    }
    
     public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }
    
    /**
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }
    
    public function hasLogger()
    {
        return (bool) $this->_logger;
    }
}