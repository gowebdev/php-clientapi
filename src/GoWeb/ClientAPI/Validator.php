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
    
    private function _checkVodFeed()
    {
        
    }
    
    protected function _checkVodCategories()
    {
        $url = '/vod/genres';

        // response
        $response = $this->_clientAPI
            ->createRequest('FilmCategories')
            ->getResponse()
            ->toArray();

        // validation errors
        if (!isset($response['categories'])) {
            $this->recordError($url, 'categories', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['categories'])) {
            $this->recordError($url, 'categories', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {

            if (count($response['categories']) < 1) {
                $this->recordError($url, 'categories.id', self::ERROR_TYPE_FIELD_REQUIRED);
                $this->recordError($url, 'categories.name', self::ERROR_TYPE_FIELD_REQUIRED);
            }

            foreach ($response['categories'] as $cat) {
                if (!is_array($cat)) {
                    $this->recordError($url, 'categories', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                    continue;
                }

                // id
                if (!isset($cat['id'])) {
                    $this->recordError($url, 'categories.id', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif ((string)(int)$cat['id'] !== (string)$cat['id']) {
                    $this->recordError($url, 'categories.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                }

                // name
                if (!isset($cat['name'])) {
                    $this->recordError($url, 'categories.name', self::ERROR_TYPE_FIELD_REQUIRED);

                } elseif (!is_string($cat['name'])) {
                    $this->recordError($url, 'categories.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                }

                // genres
                if (isset($cat['genres'])) {

                    if (!is_array($cat['genres'])) {
                        $this->recordError($url, 'categories.genres', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

                    } else {

                        foreach ($cat['genres'] as $genre) {

                            if (!is_array($genre)) {
                                $this->recordError($url, 'categories.genres', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
                                continue;
                            }

                            // id
                            if (!isset($genre['id'])) {
                                $this->recordError($url, 'categories.genres.id', self::ERROR_TYPE_FIELD_REQUIRED);

                            } elseif ((string)(int)$genre['id'] !== (string)$genre['id']) {
                                $this->recordError($url, 'categories.genres.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
                            }

                            // name
                            if (!isset($genre['name'])) {
                                $this->recordError($url, 'categories.genres.name', self::ERROR_TYPE_FIELD_REQUIRED);

                            } elseif (!is_string($genre['name'])) {
                                $this->recordError($url, 'categories.genres.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
                            }
                        }
                    }
                }
            }
        }
    }
}