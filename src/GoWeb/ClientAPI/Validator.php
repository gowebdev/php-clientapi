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
    
    private function _checkChannelsEpg()
    {
        
    }
    
    protected function _checkVodFeed()
    {
        $url = '/vod/feed';

        // response
        $response = $this->_clientAPI
            ->createRequest('Films')
            ->getResponse()
            ->toArray();

        // validation errors

        //total_items
        if (!isset($response['total_items'])) {
            $this->recordError($url, 'total_items', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif ((string) (int) $response['total_items'] !== (string) $response['total_items']) {
            $this->recordError($url, 'total_items', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

        //items
        if (!isset($response['items'])) {
            $this->recordError($url, 'items', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['items'])) {
            $this->recordError($url, 'items', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            if (count($response['items']) < 1) {
                $this->recordError($url, 'items.id', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'items.name', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'items.description', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'items.category', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'items.filesize', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'items.thumb', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'items.ad', self::ERROR_TYPE_FIELD_REQUIRED);
            }

            foreach ($response['items'] as $item) {

                // items.id
                if (!isset($item['id'])) {
                    $this->recordError($url, 'items.id', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif ((string) (int) $item['id'] !== (string) $item['id']) {
                    $this->recordError($url, 'items.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // items.name
                if (!isset($item['name'])) {
                    $this->recordError($url, 'items.name', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($item['name'])) {
                    $this->recordError($url, 'items.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.description
                if (!isset($item['description'])) {
                    $this->recordError($url, 'items.description', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($item['description'])) {
                    $this->recordError($url, 'items.description', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.hd
                if (isset($item['hd'])) {
                    if ((string) (int) $item['hd'] !== (string) $item['hd']) {
                        $this->recordError($url, 'items.hd', self::ERROR_TYPE_FIELD_MUSTBEINT);

                    } elseif (!in_array((int)$item['hd'], [0, 1])) {
                        $this->recordError($url, 'items.hd', self::ERROR_TYPE_FIELD_OUTOFRANGE);
                    }
                }

                // items.year
                if (isset($item['year']) && (string)(int) $item['year'] !== (string) $item['year']) {
                    $this->recordError($url, 'items.year', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // items.rate
                if (isset($item['rate'])) {
                    if ((string) (int) $item['rate'] !== (string) $item['rate']) {
                        $this->recordError($url, 'items.rate', self::ERROR_TYPE_FIELD_MUSTBEINT);

                    } elseif (!in_array((int)$item['rate'], [1, 2, 3, 4, 5])) {
                        $this->recordError($url, 'items.rate', self::ERROR_TYPE_FIELD_OUTOFRANGE);
                    }
                }

                // items.category
                if (!isset($item['category'])) {
                    $this->recordError($url, 'items.category', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif ((string) (int) $item['category'] !==  (string) $item['category']) {
                    $this->recordError($url, 'items.category', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // items.genres
                if (isset($item['genres']) && !is_array($item['genres'])) {
                    $this->recordError($url, 'items.genres', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                }

                // items.director
                if (isset($item['director']) && !is_string($item['director'])) {
                    $this->recordError($url, 'items.director', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.actors
                if (isset($item['actors']) && !is_string($item['actors'])) {
                    $this->recordError($url, 'items.actors', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.country
                if (isset($item['country']) && !is_string($item['country'])) {
                    $this->recordError($url, 'items.country', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.duration
                if (isset($item['duration']) && (string)(int) $item['duration'] !== (string) $item['duration']) {
                    $this->recordError($url, 'items.duration', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // items.url
                if (isset($item['url']) && !is_string($item['url'])) {
                    $this->recordError($url, 'items.url', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.torrent
                if (isset($item['torrent']) && !is_string($item['torrent'])) {
                    $this->recordError($url, 'items.torrent', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.hlts1
                if (isset($item['hlts1']) && !is_string($item['hlts1'])) {
                    $this->recordError($url, 'items.hlts1', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.filesize
                if (!isset($item['filesize'])) {
                    $this->recordError($url, 'items.filesize', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif ((string)(int) $item['filesize'] !== (string) $item['filesize']) {
                    $this->recordError($url, 'items.filesize', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // items.thumb
                if (!isset($item['thumb'])) {
                    $this->recordError($url, 'items.thumb', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($item['thumb'])) {
                    $this->recordError($url, 'items.thumb', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // items.ad
                if (!isset($item['ad'])) {
                    $this->recordError($url, 'items.ad', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($item['ad']) && !is_array($item['ad'])) {
                    $this->recordError($url, 'items.ad', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                    $this->recordError($url, 'items.ad', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                }
            }
        }
    }
    
    private function _checkVodCategories()
    {
        
    }
}