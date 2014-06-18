<?php

namespace GoWeb\ClientAPI;

class Validator
{
    const ERROR_TYPE_FIELD_REQUIRED = 0;
    const ERROR_TYPE_FIELD_EMPTY = 1;
    const ERROR_TYPE_FIELD_MUSTBESTRING = 2;
    const ERROR_TYPE_FIELD_MUSTBEINT = 3;
    const ERROR_TYPE_FIELD_MUSTBEBOOL = 4;
    const ERROR_TYPE_FIELD_MUSTBEARRAY = 5;
    const ERROR_TYPE_FIELD_MUSTBEFLOAT = 6;
    const ERROR_TYPE_FIELD_OUTOFRANGE = 7;
    const ERROR_TYPE_FIELD_OVERLENGTHLIMIT = 8;
    const ERROR_TYPE_FIELD_WRONGDATEFORMAT = 9;
    const ERROR_TYPE_FIELD_MUSTBETIMESTAMP = 10;

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
        
    }
    
    private function _checkChannelsList()
    {
        
    }
    
    protected  function _checkChannelsEpg()
    {
        $url = '/channels/epg';

        // response
        $response = $this->_clientAPI
            ->createRequest('Epg')
            ->getResponse()
            ->toArray();

        // validation errors
        if (!isset($response['epg'])) {
            $this->recordError($url, 'epg', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['epg'])) {
            $this->recordError($url, 'epg', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            foreach ($response['epg'] as $epg) {

                if (!is_array($epg)) {
                    $this->recordError($url, 'epg', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                    continue;
                }

                foreach ($epg as $program) {

                    if (!is_array($program)) {
                        $this->recordError($url, 'epg', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                        continue;
                    }

                    // name
                    if (!isset($program['name'])) {
                        $this->recordError($url, 'epg.name', self::ERROR_TYPE_FIELD_REQUIRED);

                    } elseif (!is_string($program['name'])) {
                        $this->recordError($url, 'epg.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                    }

                    // from
                    if (!isset($program['from'])) {
                        $this->recordError($url, 'epg.from', self::ERROR_TYPE_FIELD_REQUIRED);

                    } elseif ((string)(int)$program['from'] !== (string)$program['from']) {
                        $this->recordError($url, 'epg.from', self::ERROR_TYPE_FIELD_MUSTBETIMESTAMP);

                    }

                    // to
                    if (!isset($program['to'])) {
                        $this->recordError($url, 'epg.to', self::ERROR_TYPE_FIELD_REQUIRED);

                    } elseif ((string)(int)$program['to'] !== (string)$program['to']) {
                        $this->recordError($url, 'epg.to', self::ERROR_TYPE_FIELD_MUSTBETIMESTAMP);
                    }

                    // url
                    if (isset($program['url']) && !is_string($program['url'])) {
                        $this->recordError($url, 'epg.url', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                    }

                    // torrent
                    if (isset($program['torrent']) && !is_string($program['torrent'])) {
                        $this->recordError($url, 'epg.torrent', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                    }

                }
            }
        }
    }
    
    private function _checkVodFeed()
    {
        
    }
    
    private function _checkVodCategories()
    {
        
    }
}