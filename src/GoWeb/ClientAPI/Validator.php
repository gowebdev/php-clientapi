<?php

namespace GoWeb\ClientAPI;

use GoWeb\ClientAPI\Validator\Exception\EmptyResponse;

class Validator
{
    const ERROR_TYPE_FIELD_REQUIRED = 0;
    const ERROR_TYPE_FIELD_EMPTY = 1;
    const ERROR_TYPE_FIELD_MUSTBESTRING = 2;
    const ERROR_TYPE_FIELD_MUSTBEINT = 3;
    const ERROR_TYPE_FIELD_MUSTBEBOOL = 4;
    const ERROR_TYPE_FIELD_MUSTBEARRAY = 5;
    const ERROR_TYPE_FIELD_OUTOFRANGE = 6;
    const ERROR_TYPE_FIELD_OVERLENGTHLIMIT = 7;


    protected $_status = array(
        0 => 'Authorization successfull',
        1 => 'Server error (generic)',
        2 => 'Wrong credentials',
        3 => 'Account blocked',
        4 => 'Email not confirmed yet',
        5 => 'Client version not supported',
        6 => 'No active service found (Client myst register some service in personal cabinet)',
        7 => 'passed service is wrong'
    );

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
    
    protected  function _checkAuth()
    {
        // auth request
        $request = $this->_clientAPI->auth();

        // get requested url
        $url = $request->getUrl();

        // response
        $response = $request
            ->getResponse()
            ->toArray();

        if (!$response) {
          throw new \GoWeb\ClientAPI\Validator\Exception\EmptyResponse('Empty response');
        }

        // token
        if (!isset($response['token'])) {
            $this->recordError($url, 'token', self::ERROR_TYPE_FIELD_REQUIRED);
        } elseif (!is_string($response['token'])) {
                $this->recordError($url, 'token', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // status
        if (!isset($response['status'])) {
            $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_REQUIRED);
        } else {
            if (!is_int($response['status'])) {
                $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }

            if (!in_array($response['status'], $this->_status)) {
                $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
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