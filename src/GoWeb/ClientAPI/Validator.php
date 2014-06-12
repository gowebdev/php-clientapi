<?php

namespace GoWeb\ClientAPI;

class Validator
{
    const ERROR_TYPE_FIELD_REQUIRED = 0;
    const ERROR_TYPE_FIELD_EMPTY = 1;
    const ERROR_TYPE_FIELD_MUSTBEARRAY = 2;
    const ERROR_TYPE_FIELD_MUSTBEINT = 3;
    const ERROR_TYPE_FIELD_MUSTBESTRING = 4;
    const ERROR_TYPE_FIELD_MUSTBEBOOL = 5;
    const ERROR_TYPE_FIELD_OUTOFRANGE = 6;
    const ERROR_TYPE_FIELD_OVERLENGTHLIMIT = 7;

    /**
     *
     * @var \GoWeb\ClientAPI
     */
    private $_clientAPI;
    
    /**
     *
     * @var array
     */
    protected $_errors;
    
    public function __construct(\GoWeb\ClientAPI $clientAPI)
    {
        $this->_clientAPI = $clientAPI;
    }
    
    public function isValid()
    {
        try {
            if(null === $this->_errors) {
                $this->_errors = array();

                // check meta
                $this->_checkMeta();

                // check services
                $this->_checkServices();

                // check auth
                $this->_checkAuth();

                // check channels
                $this->_checkChannelsList();
                $this->_checkChannelsEpg();

                // check films
                $this->_checkVodFeed();
                $this->_checkVodCategories();
            }

            return !$this->_errors;
        } catch(\Guzzle\Http\Exception\CurlException $e) {
            switch($e->getErrorNo()) {
                case CURLE_COULDNT_RESOLVE_HOST:
                    throw new \GoWeb\ClientAPI\Validator\Exception\UnknownHost('Host not found');
                default:
                    throw $e;
            }
        }
    }
    
    public function getReport()
    {
        return $this->_errors;
    }
    
    private function recordError($url, $field, $errorType)
    {
        $this->_errors[$url][$field][] = $errorType;
        return $this;
    }
    
    private function _checkMeta() 
    {
        $response = $this->_clientAPI->getMeta();
        
        $url = '/';
        
        if(!$response->getName()) {
            $this->recordError($url, 'name', self::ERROR_TYPE_FIELD_EMPTY);
        }
        
        if(!$response->getIcon()) {
            $this->recordError($url, 'icon', self::ERROR_TYPE_FIELD_EMPTY);
        }
        
        if(!$response->getDescription()) {
            $this->recordError($url, 'description', self::ERROR_TYPE_FIELD_EMPTY);
        }
        
        else if(!is_array($response->getDescription())) {
            $this->recordError($url, 'description', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
        }
    }
     
    private function _checkServices()
    {
        
    }
    
    private function _checkAuth()
    {
        // get profile
        $profile = $this->_clientAPI
            ->auth()
            ->getResponse()
            ->profile;

        $url = '/';

        // api_key
        if (!isset($profile['api_key'])) {
            $this->recordError($url, 'api_key', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['api_key'])) {
            $this->recordError($url, 'api_key', self::ERROR_TYPE_FIELD_MUSTBESTRING);

        } elseif (strlen($profile['api_key']) > 40) {
            $this->recordError($url, 'api_key', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
        }

        // email
        if (isset($profile['email'])) {

            if (!is_string($profile['email'])) {
                $this->recordError($url, 'email', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (strlen($profile['email']) > 40) {
                $this->recordError($url, 'email', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
            }
        }

        // password
        if (isset($profile['password']) && !is_string($profile['password'])) {
            $this->recordError($url, 'password', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // agent
        if (isset($profile['agent']) && !is_string($profile['agent'])) {
            $this->recordError($url, 'agent', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // remember
        if (isset($profile['remember']) && !is_bool($profile['remember'])) {
            $this->recordError($url, 'remember', self::ERROR_TYPE_FIELD_MUSTBEBOOL);
        }

        // permid
        if (isset($profile['permid']) && !is_string($profile['permid'])) {
            $this->recordError($url, 'permid', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // service_id
        if (isset($profile['service_id']) && !is_int($profile['service_id'])) {
            $this->recordError($url, 'service_id', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

        // application_version
        if (isset($profile['application_version']) && !is_string($profile['application_version'])) {
            $this->recordError($url, 'application_version', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // os_type
        if (isset($profile['os_type']) && !is_string($profile['os_type'])) {
            $this->recordError($url, 'os_type', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // os_version
        if (isset($profile['os_version']) && !is_string($profile['os_version'])) {
            $this->recordError($url, 'os_version', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // device_model
        if (isset($profile['device_model']) && !is_string($profile['device_model'])) {
            $this->recordError($url, 'device_model', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // uuid
        if (isset($profile['uuid'])) {

            if (!is_string($profile['uuid'])) {
                $this->recordError($url, 'uuid', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (strlen($profile['uuid']) > 16) {
                $this->recordError($url, 'uuid', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
            }
        }

        // device_id
        if (isset($profile['device_id']) && !is_string($profile['device_id'])) {
            $this->recordError($url, 'device_id', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // advertising_id
        if (isset($profile['advertising_id']) && !is_string($profile['advertising_id'])) {
            $this->recordError($url, 'advertising_id', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // vendor_id
        if (isset($profile['vendor_id']) && !is_string($profile['vendor_id'])) {
            $this->recordError($url, 'vendor_id', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // mac
        if (isset($profile['mac']) && !is_string($profile['mac'])) {
            $this->recordError($url, 'mac', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // ip
        if (isset($profile['ip']) && !is_string($profile['ip'])) {
            $this->recordError($url, 'ip', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // referal
        if (isset($profile['referal']) && !is_string($profile['referal'])) {
            $this->recordError($url, 'referal', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // expiration
        if (isset($profile['expiration']) && !is_int($profile['expiration'])) {
            $this->recordError($url, 'expiration', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // session
        if (isset($profile['session']) && !is_bool($profile['session'])) {
            $this->recordError($url, 'session', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }
    }
    
    private function _checkChannelsList()
    {
        
    }
    
    private function _checkChannelsEpg()
    {
        
    }
    
    private function _checkVodFeed()
    {
        
    }
    
    private function _checkVodCategories()
    {
        
    }
}