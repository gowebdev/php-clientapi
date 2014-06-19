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
    const ERROR_TYPE_FIELD_MUSTBEFLOAT = 6;
    const ERROR_TYPE_FIELD_OUTOFRANGE = 7;
    const ERROR_TYPE_FIELD_OVERLENGTHLIMIT = 8;
    const ERROR_TYPE_FIELD_WRONGDATEFORMAT = 9;

    // preset data
    private $_presets = array(

        'status' => array(
            0 => 'Authorization successfull',
            1 => 'Server error (generic)',
            2 => 'Wrong credentials',
            3 => 'Account blocked',
            4 => 'Email not confirmed yet',
            5 => 'Client version not supported',
            6 => 'No active service found (Client myst register some service in personal cabinet)',
            7 => 'passed service is wrong'
        ),

        'gender' => array('MALE','FEMALE'),

        'profileStatus' => array(
            'ACTIVE',
            'SUSPENDED',
            'BLOCKED',
            'CLOSED'
        ),
        'chargeoffPeriod' => array('DAILY','MONTHLY')
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

    private $_lastRequestedUrl;
    
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
    
    protected  function _checkAuth()
    {
        // auth request
        $request = $this->_clientAPI->auth();

        // get requested url
        $url = $this->_lastRequestedUrl = '/users/authorize';

        // response
        $response = $request
            ->getResponse()
            ->toArray();

        if (!$response) {
          throw new \GoWeb\ClientAPI\Validator\Exception\EmptyResponse('Empty response');
        }


        // status
        if (!isset($response['status'])) {
            $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_REQUIRED);

        } else {
            if (!is_int($response['status'])) {
                $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }

            if (!in_array($response['status'], $this->_presets['status'])) {
                $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }

        // token
        if (!isset($response['token'])) {
            $this->recordError($url, 'token', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($response['token'])) {
            $this->recordError($url, 'token', self::ERROR_TYPE_FIELD_MUSTBESTRING);

        } elseif (strlen($response['token']) > 32) {
            $this->recordError($this->_lastRequestedUrl, 'token', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
        }

        // permid
        if (isset($response['permid']) && !is_string($response['permid'])) {
            $this->recordError($url, 'permid', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // profile
        if (!isset($response['profile'])) {
            $this->recordError($url, 'profile', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['profile'])) {
            $this->recordError($url, 'profile', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            $this->_checkProfileDictionary($response['profile']);
        }

        // balance
        if (!isset($response['balance'])) {
            $this->recordError($url, 'balance', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['balance'])) {
            $this->recordError($url, 'balance', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            $this->_checkBalanceDictionary($response['balance']);
        }

        // baseServices
        if (!isset($response['baseServices'])) {
            $this->recordError($url, 'baseServices', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['baseServices'])) {
            $this->recordError($url, 'baseServices', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

        } else {
            $this->_checkClientBaseService($response['baseServices']);
        }

        // activeBaseService
        if (!isset($response['activeBaseService'])) {
            $this->recordError($url, 'activeBaseService', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_int($response['activeBaseService'])) {
            $this->recordError($url, 'activeBaseService', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

        // speed
        if (!isset($response['speed'])) {
            $this->recordError($url, 'speed', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_array($response['speed'])) {
            $this->recordError($url, 'speed', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
        }

        // rechargePage
        if (!isset($response['rechargePage'])) {
            $this->recordError($url, 'rechargePage', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($response['rechargePage'])) {
            $this->recordError($url, 'rechargePage', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }


        // profilePage
        if (!isset($response['profilePage'])) {
            $this->recordError($url, 'profilePage', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($response['profilePage'])) {
            $this->recordError($url, 'profilePage', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

    }

    private function _checkProfileDictionary(array $profile)
    {
        // id
        if (!isset($profile['id'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.id', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_int($profile['id'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

        // email
        if (!isset($profile['email'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.email', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['email'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.email', self::ERROR_TYPE_FIELD_MUSTBESTRING);

        } elseif (strlen($profile['email']) > 40) {
            $this->recordError($this->_lastRequestedUrl, 'profile.email', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
        }

        // hash
        if (!isset($profile['hash'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.hash', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['hash'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.hash', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // last_name
        if (isset($profile['last_name'])) {
            if (!is_string($profile['last_name'])) {
                $this->recordError($this->_lastRequestedUrl, 'profile.last_name', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (strlen($profile['last_name']) > 30) {
                $this->recordError($this->_lastRequestedUrl, 'profile.last_name', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
            }
        }

        // first_name
        if (isset($profile['first_name'])) {
            if (!is_string($profile['first_name'])) {
                $this->recordError($this->_lastRequestedUrl, 'profile.first_name', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (strlen($profile['first_name']) > 30) {
                $this->recordError($this->_lastRequestedUrl, 'profile.first_name', self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
            }
        }

        // gender
        if (isset($profile['gender'])) {
            if (!is_string($profile['gender'])) {
                $this->recordError($this->_lastRequestedUrl, 'profile.gender', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (!in_array($profile['gender'], $this->_presets['gender'])) {
                $this->recordError($this->_lastRequestedUrl, 'profile.gender', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }

        // birthday
        // type: string (yyyy-mm-dd), timestamp
        if (isset($profile['birthday'])) {
            if (is_string($profile['birthday'])) {
                $_date = explode('-', $profile['birthday']);
                if (count($_date) != 3 || !checkdate($_date[1], $_date[2], $_date[0])) {
                    $this->recordError($this->_lastRequestedUrl, 'profile.birthday', self::ERROR_TYPE_FIELD_WRONGDATEFORMAT);
                }
            } elseif (!is_int($profile['birthday'])) {
                $this->recordError($this->_lastRequestedUrl, 'profile.birthday', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }
        }

        // contract_number
        if (!isset($profile['contract_number'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.contract_number', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['contract_number'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.contract_number', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // status
        if (!isset($profile['status'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.status', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['status'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.status', self::ERROR_TYPE_FIELD_MUSTBESTRING);

        } elseif (!in_array($profile['status'], $this->_presets['profileStatus'])) {
            $this->recordError($this->_lastRequestedUrl, 'profile.status', self::ERROR_TYPE_FIELD_OUTOFRANGE);
        }

        // tester
        if (isset($profile['tester'])) {
            if (!is_int($profile['tester'])) {
                $this->recordError($this->_lastRequestedUrl, 'profile.tester', self::ERROR_TYPE_FIELD_MUSTBEINT);

            } elseif (!in_array($profile['tester'], array(0, 1, "0", "1"), true)) {
                $this->recordError($this->_lastRequestedUrl, 'profile.tester', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }
    }

    private function _checkBalanceDictionary(array $balance)
    {
        // amount
        if (!isset($balance['amount'])) {
            $this->recordError($this->_lastRequestedUrl, 'balance.amount', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_numeric($balance['amount'])) {
            $this->recordError($this->_lastRequestedUrl, 'balance.amount', self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
        }

        // currency
        if (!isset($balance['currency'])) {
            $this->recordError($this->_lastRequestedUrl, 'balance.currency', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($balance['currency'])) {
            $this->recordError($this->_lastRequestedUrl, 'balance.currency', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }
    }

    private function _checkClientBaseService(array $baseServices)
    {
        foreach ($baseServices as $baseService) {

            // id
            if (!isset($baseService['id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.id', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_int($baseService['id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }

            // custom_name
            if (!isset($baseService['custom_name'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.custom_name', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_string($baseService['custom_name'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.custom_name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
            }

            // service_id
            if (!isset($baseService['service_id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.service_id', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_int($baseService['service_id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.service_id', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }

            // name
            if (!isset($baseService['name'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.name', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_string($baseService['name'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
            }

            // cost
            if (!isset($baseService['cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.cost', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_numeric($baseService['cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.cost', self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
            }

            // total_cost
            if (!isset($baseService['total_cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.total_cost', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_numeric($baseService['total_cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.total_cost', self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
            }

            // total_monthly_cost
            if (!isset($baseService['total_monthly_cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.total_monthly_cost', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_numeric($baseService['total_monthly_cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.total_monthly_cost', self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
            }

            // chargeoff_period
            if (!isset($baseService['chargeoff_period'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.chargeoff_period', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_string($baseService['chargeoff_period'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.chargeoff_period', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (!in_array($baseService['chargeoff_period'], $this->_presets['chargeoffPeriod'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.chargeoff_period', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }

            // additional
            if (isset($baseService['additional'])) {

                if (!is_array($baseService['additional'])) {
                    $this->recordError($this->_lastRequestedUrl, 'baseServices.additional', self::ERROR_TYPE_FIELD_MUSTBEARRAY);

                } else {
                    $this->_checkAdditional($baseService['additional']);
                }
            }

            // ad
            if (isset($baseService['ad']) && !in_array($baseService['ad'], array(true, false, 0, 1, "0", "1"), true)) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.ad', self::ERROR_TYPE_FIELD_MUSTBEBOOL);
            }

            // catchup
            if (isset($baseService['catchup']) && !in_array($baseService['catchup'], array(true, false, 0, 1, "0", "1"), true)) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.catchup', self::ERROR_TYPE_FIELD_MUSTBEBOOL);
            }

            // stb
            if (isset($baseService['stb']) && !is_array($baseService['stb'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.stb', self::ERROR_TYPE_FIELD_MUSTBEARRAY);
            }

        }
    }

    private function _checkAdditional(array $additionalServices)
    {
        if (!$additionalServices) {
            $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.id', self::ERROR_TYPE_FIELD_REQUIRED);
            $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.service_id', self::ERROR_TYPE_FIELD_REQUIRED);
            $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.custom_name', self::ERROR_TYPE_FIELD_REQUIRED);
            $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.cost', self::ERROR_TYPE_FIELD_REQUIRED);
            $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.chargeoff_period', self::ERROR_TYPE_FIELD_REQUIRED);
        }

        foreach ($additionalServices as $additional) {
            // id
            if (!isset($additional['id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.id', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_int($additional['id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.id', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }

            // service_id
            if (!isset($additional['service_id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.service_id', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_int($additional['service_id'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.service_id', self::ERROR_TYPE_FIELD_MUSTBEINT);
            }

            // custom_name
            if (!isset($additional['custom_name'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.custom_name', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_string($additional['custom_name'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.custom_name', self::ERROR_TYPE_FIELD_MUSTBESTRING);
            }

            // cost
            if (!isset($additional['cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.cost', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_numeric($additional['cost'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.cost', self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
            }

            // chargeoff_period
            if (!isset($additional['chargeoff_period'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.chargeoff_period', self::ERROR_TYPE_FIELD_REQUIRED);

            } elseif (!is_string($additional['chargeoff_period'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.chargeoff_period', self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (!in_array($additional['chargeoff_period'], $this->_presets['chargeoffPeriod'])) {
                $this->recordError($this->_lastRequestedUrl, 'baseServices.additional.chargeoff_period', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }
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
