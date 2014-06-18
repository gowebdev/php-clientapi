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
     
    protected function _checkServices()
    {
        $url = '/services';

        // response
        $response = $this->_clientAPI
            ->createRequest('Services')
            ->getResponse();

        if (!$response) {
            throw new \GoWeb\ClientAPI\Validator\Exception\EmptyResponse('Empty response');
        }

        // packets
        if (!$response->getPackets()) {
            $this->recordError($url, 'packets', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response->getPackets())) {
            $this->recordError($url, 'packets', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            foreach ($response->getPackets() as $packet) {

                // id
                if (!isset($packet['id'])) {
                    $this->recordError($url, 'packets.id', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_int($packet['id'])) {
                    $this->recordError($url, 'packets.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // name
                if (!isset($packet['name'])) {
                    $this->recordError($url, 'packets.name', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($packet['name'])) {
                    $this->recordError($url, 'packets.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // cost
                if (!isset($packet['cost'])) {
                    $this->recordError($url, 'packets.cost', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_numeric($packet['cost'])) {
                    $this->recordError($url, 'packets.cost', self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
                }

                // type
                if (!isset($packet['type'])) {
                    $this->recordError($url, 'packets.type', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($packet['type'])) {
                    $this->recordError($url, 'packets.type', self::ERROR_TYPE_FIELD_MUSTBESTRING);

                } elseif (!in_array($packet['type'], ['BASE','ADDITIONAL'])) {
                    $this->recordError($url, 'packets.type', self::ERROR_TYPE_FIELD_OUTOFRANGE);
                }

                // channels
                if (!isset($packet['channels'])) {
                    $this->recordError($url, 'packets.channels', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_array($packet['channels'])) {
                    $this->recordError($url, 'packets.channels', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                }
            }

        }
        // channels
        if (!$response->getChannels()) {
            $this->recordError($url, 'channels', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response->getChannels())) {
            $this->recordError($url, 'channels', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            foreach ($response->getChannels() as $channel) {

                // id
                if (!isset($channel['id'])) {
                    $this->recordError($url, 'channels.id', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_int($channel['id'])) {
                    $this->recordError($url, 'channels.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // name
                if (!isset($channel['name'])) {
                    $this->recordError($url, 'channels.name', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($channel['name'])) {
                    $this->recordError($url, 'channels.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // logo
                if (!isset($channel['logo'])) {
                    $this->recordError($url, 'channels.logo', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($channel['logo'])) {
                    $this->recordError($url, 'channels.logo', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }
            }
        }
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