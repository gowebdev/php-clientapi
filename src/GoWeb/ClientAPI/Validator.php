<?php

namespace GoWeb\ClientAPI;

class Validator
{
    const ERROR_TYPE_FIELD_EMPTY = 0;
    const ERROR_TYPE_FIELD_MUSTBEARRAY = 0;
    
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
        if(null !== $this->_errors) {
            return (bool) $this->_errors;
        }
        
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
        $request = $this->_clientAPI->createRequest('Meta');
        
        /* @var $response \GoWeb\ClientAPI\Response\Meta */
        $response = $request->send();
        
        $url = $request->getUrl();
        
        if(!$response->getName()) {
            $this->_recordError($url, 'name', self::ERROR_TYPE_FIELD_EMPTY);
        }
        
        if(!$response->getIcon()) {
            $this->_recordError($url, 'icon', self::ERROR_TYPE_FIELD_EMPTY);
        }
        
        if(!$response->getDescription()) {
            $this->_recordError($url, 'description', self::ERROR_TYPE_FIELD_EMPTY);
        }
        
        else if(!is_array($response->getDescription())) {
            $this->_recordError($url, 'description', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
        }
    }
     
    private function _checkServices()
    {
        
    }
    
    private function _checkAuth()
    {
        
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