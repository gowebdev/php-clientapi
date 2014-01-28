<?php

namespace GoWeb;

use Guzzle\Http\Client;

use GoWeb\ClientAPI\ICacheAdapter;

class ClientAPI
{
    private $_apiServerUrl;

    private $_email = '';
    
    private $_password = '';
    
    private $_connection;

    /**
     *
     * @var \GoWeb\ClientAPI\ICacheAdapter
     */
    private $_cacheAdapter;

    private $_language;

    public function __construct(array $options = null)
    {
        // configure api
        if($options) {
            if(isset($options['apiServerUrl'])) {
                $this->setAPIServerUrl($options['apiServerUrl']);
            }
        }
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

    public function setCacheAdapter(ICacheAdapter $cahce)
    {
        $this->_cacheAdapter = $cahce;

        return $this;
    }

    /**
     *
     * @return \GoWeb\ClientAPI\ICacheAdapter
     */
    public function getCacheAdapter()
    {
        return $this->_cacheAdapter;
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
        return !!$this->getActiveUser();
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
        $this->email = $email;
        $this->_password = $password;
        
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
        
        return $authQuery;
    }

    public function logout()
    {
        $this->_activeUser = null;
    }
}