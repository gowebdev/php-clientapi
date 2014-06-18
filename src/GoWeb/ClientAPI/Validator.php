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
    
    protected  function _checkChannelsList()
    {
        $url = '/channels/list';

        // response
        $response = $this->_clientAPI
            ->createRequest('Channels')
            ->getResponse()
            ->toArray();

        // validation errors
        if (!isset($response['channels'])) {
            $this->recordError($url, 'channels', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['channels'])) {
            $this->recordError($url, 'channels', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            foreach ($response['channels'] as $channel) {

                if (!is_array($channel)) {
                    $this->recordError($url, 'channels', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                    continue;
                }

                // name
                if (!isset($channel['name'])) {
                    $this->recordError($url, 'channels.name', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($channel['name'])) {
                    $this->recordError($url, 'channels.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // url
                if (!isset($channel['url'])) {
                    $this->recordError($url, 'channels.url', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($channel['url'])) {
                    $this->recordError($url, 'channels.url', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // torrent
                if (isset($channel['torrent']) && !is_string($channel['torrent'])) {
                    $this->recordError($url, 'channels.torrent', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // hlts1
                if (isset($channel['hlts1']) && !is_string($channel['hlts1'])) {
                    $this->recordError($url, 'channels.hlts1', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // genre
                if (!isset($channel['genre'])) {
                    $this->recordError($url, 'channels.genre', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($channel['genre'])) {
                    $this->recordError($url, 'channels.genre', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // genre_id
                if (!isset($channel['genre_id'])) {
                    $this->recordError($url, 'channels.genre_id', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif ((string)(int) $channel['genre_id'] !==  (string)$channel['genre_id']) {
                    $this->recordError($url, 'channels.genre_id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // channel_id
                if (!isset($channel['channel_id'])) {
                    $this->recordError($url, 'channels.channel_id', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif ((string)(int) $channel['channel_id'] !==  (string)$channel['channel_id']) {
                    $this->recordError($url, 'channels.channel_id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // logo
                if (isset($channel['logo']) && !is_string($channel['logo'])) {
                    $this->recordError($url, 'channels.logo', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // hd
                if (isset($channel['hd'])) {

                    if (!is_numeric($channel['hd'])) {
                        $this->recordError($url, 'channels.hd', self::ERROR_TYPE_FIELD_MUSTBEINT);

                    } elseif(!in_array((int)$channel['hd'], [0, 1])) {
                        $this->recordError($url, 'channels.hd', self::ERROR_TYPE_FIELD_OUTOFRANGE);
                    }
                }

                // 3d
                if (isset($channel['3d'])) {

                    if (!is_numeric($channel['3d'])) {
                        $this->recordError($url, 'channels.3d', self::ERROR_TYPE_FIELD_MUSTBEINT);

                    } elseif(!in_array((int)$channel['3d'], [0, 1])) {
                        $this->recordError($url, 'channels.3d', self::ERROR_TYPE_FIELD_OUTOFRANGE);
                    }
                }

                // fav
                if (isset($channel['fav'])) {

                    if (!is_numeric($channel['fav'])) {
                        $this->recordError($url, 'channels.fav', self::ERROR_TYPE_FIELD_MUSTBEINT);

                    } elseif(!in_array((int)$channel['fav'], [0, 1])) {
                        $this->recordError($url, 'channels.fav', self::ERROR_TYPE_FIELD_OUTOFRANGE);
                    }
                }

                // ad
                if (!isset($channel['ad'])) {
                    $this->recordError($url, 'channels.ad', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($channel['ad']) && !is_array($channel['ad'])) {
                    $this->recordError($url, 'channels.ad', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                    $this->recordError($url, 'channels.ad', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                }
            }
        }
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