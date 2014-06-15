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
    private $presets = array(

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
        ));

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
    
    private function recordError($url, $fields, $errorType)
    {
        if (!is_array($fields)) {
            $this->_errors[$url][$fields][] = $errorType;

        } else {

            if (!isset($this->_errors[$url])) {
                $this->_errors[$url] = array();
            }

            array_walk_recursive($fields, function(&$field) use($errorType) {
                if (!is_array($field)) {
                    $_field = $field;
                    $field = array();
                }
                $field[$_field][] = $errorType;
            });

            $this->_errors[$url] = array_merge_recursive($this->_errors[$url], $fields);

        }

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
        $url = $this->_lastRequestedUrl = $request->getUrl();

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

            if (!in_array($response['status'], $this->presets['status'])) {
                $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }

        // permid
        if (isset($response['permid']) && !is_string($response['permid'])) {
            $this->recordError($url, 'status', self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // profile
        if (!isset($response['profile'])) {
            $this->recordError($url, 'profile', self::ERROR_TYPE_FIELD_REQUIRED);

        } else {
            $this->_checkProfileDictionary($response['profile'], $url);
        }

        // balance
        if (!isset($response['balance'])) {
            $this->recordError($url, 'balance', self::ERROR_TYPE_FIELD_REQUIRED);

        } else {
            $this->_checkBalanceDictionary($response['balance']);
        }

        // baseServices
        if (!isset($response['baseServices'])) {
            $this->recordError($url, 'baseServices', self::ERROR_TYPE_FIELD_REQUIRED);

        } else {
            // @TODO: check balance fields
        }

        // activeBaseService
        if (!isset($response['activeBaseService'])) {
            $this->recordError($url, 'activeBaseService', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_int($response['activeBaseService'])) {
            $this->recordError($url, 'activeBaseService', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

        // rechargePage
        if (!isset($response['rechargePage'])) {
            $this->recordError($url, 'rechargePage', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($response['rechargePage'])) {
            $this->recordError($url, 'rechargePage', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }


        // profilePage
        if (!isset($response['profilePage'])) {
            $this->recordError($url, 'profilePage', self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($response['profilePage'])) {
            $this->recordError($url, 'profilePage', self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

    }

    private function _checkProfileDictionary(array $profile, $url)
    {
        // id
        if (!isset($profile['id'])) {
            $this->recordError($url, array('profile' => 'id'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_int($profile['id'])) {
            $this->recordError($url, array('profile' => 'id'), self::ERROR_TYPE_FIELD_MUSTBEINT);
        }

        // email
        if (!isset($profile['email'])) {
            $this->recordError($url, array('profile' => 'email'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['email'])) {
            $this->recordError($url, array('profile' => 'email'), self::ERROR_TYPE_FIELD_MUSTBESTRING);

        } elseif (strlen($profile['email']) > 40) {
            $this->recordError($url, array('profile' => 'email'), self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
        }

        // hash
        if (!isset($profile['hash'])) {
            $this->recordError($url, array('profile' => 'hash'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['hash'])) {
            $this->recordError($url, array('profile' => 'hash'), self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // last_name
        if (isset($profile['last_name'])) {
            if (!is_string($profile['last_name'])) {
                $this->recordError($url, array('profile' => 'last_name'), self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (strlen($profile['last_name']) > 30) {
                $this->recordError($url, array('profile' => 'last_name'), self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
            }
        }

        // first_name
        if (isset($profile['first_name'])) {
            if (!is_string($profile['first_name'])) {
                $this->recordError($url, array('profile' => 'first_name'), self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (strlen($profile['first_name']) > 30) {
                $this->recordError($url, array('profile' => 'first_name'), self::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
            }
        }

        // gender
        if (isset($profile['gender'])) {
            if (!is_string($profile['gender'])) {
                $this->recordError($url, array('profile' => 'gender'), self::ERROR_TYPE_FIELD_MUSTBESTRING);

            } elseif (!in_array($profile['gender'], $this->presets['gender'])) {
                $this->recordError($url, array('profile' => 'gender'), self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }

        // birthday
        // type: string (yyyy-mm-dd), timestamp
        if (isset($profile['birthday'])) {
            if (is_string($profile['birthday'])) {
                $_date = explode('-', $profile['birthday']);
                if (count($_date) != 3 || !checkdate($_date[1], $_date[2], $_date[0])) {
                    $this->recordError($url, array('profile' => 'birthday'), self::ERROR_TYPE_FIELD_WRONGDATEFORMAT);
                }
            } elseif (!is_int($profile['birthday'])) {
                $this->recordError($url, array('profile' => 'birthday'), self::ERROR_TYPE_FIELD_MUSTBEINT);
            }
        }

        // contract_number
        if (!isset($profile['contract_number'])) {
            $this->recordError($url, array('profile' => 'contract_number'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['contract_number'])) {
            $this->recordError($url, array('profile' => 'contract_number'), self::ERROR_TYPE_FIELD_MUSTBESTRING);
        }

        // status
        if (!isset($profile['status'])) {
            $this->recordError($url, array('profile' => 'status'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($profile['status'])) {
            $this->recordError($url, array('profile' => 'status'), self::ERROR_TYPE_FIELD_MUSTBESTRING);

        } elseif (!in_array($profile['status'], $this->presets['profileStatus'])) {
            $this->recordError($url, array('profile' => 'status'), self::ERROR_TYPE_FIELD_OUTOFRANGE);
        }

        // tester
        if (isset($profile['tester'])) {
            if (!is_int($profile['tester'])) {
                $this->recordError($url, array('profile' => 'tester'), self::ERROR_TYPE_FIELD_MUSTBEINT);

            } elseif (!in_array($profile['tester'], array(0,1))) {
                $this->recordError($url, array('profile' => 'tester'), self::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }
    }

    private function _checkBalanceDictionary(array $balance)
    {
        // amount
        if (!isset($balance['amount'])) {
            $this->recordError($this->_lastRequestedUrl, array('balance' => 'amount'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_float($balance['amount'])) {
            $this->recordError($this->_lastRequestedUrl, array('balance' => 'amount'), self::ERROR_TYPE_FIELD_MUSTBEFLOAT);
        }

        // currency
        if (!isset($balance['currency'])) {
            $this->recordError($this->_lastRequestedUrl, array('balance' => 'currency'), self::ERROR_TYPE_FIELD_REQUIRED);

        } elseif (!is_string($balance['currency'])) {
            $this->recordError($this->_lastRequestedUrl, array('balance' => 'currency'), self::ERROR_TYPE_FIELD_MUSTBESTRING);
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