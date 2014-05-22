<?php

namespace GoWeb\ClientAPI\Query;

class Auth extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/users/authorize';

    protected $_action = self::ACTION_READ;
    
    protected $_structureClassName = 'GoWeb\Api\Model\Client';

    const ERROR_NONE                                    = 0;
    const ERROR_GENERIC_SERVER_ERROR                    = 1;
    const ERROR_WRONG_CREDENTIALS                       = 2;
    const ERROR_ACCOUNT_BLOCKED                         = 3;
    const ERROR_EMAIL_NOT_CONFIRMED                     = 4;
    const ERROR_CLIENT_VERSION_NOT_SUPPORTED            = 5;
    const ERROR_NO_ACTIVE_SERVICES_FOUND                = 6;
    
    public function setIp($ip)
    {
        $this->setQueryParam('ip', $ip);

        return $this;
    }

    public function byEmail($email, $password)
    {
        $this->setQueryParam('email', $email);
        $this->setQueryParam('password', $password);

        return $this;
    }
    
    public function demo($agent = null)
    {
        $this->setQueryParam('email', null);
        $this->setQueryParam('password', null);
        
        if($agent) {
            $this->setQueryParam('agent', $agent);
        }
        
        return $this;
    }

    public function remember($remember = true)
    {
        $this->setQueryParam('remember', (int) $remember );

        return $this;
    }
    
    public function setAPIKey($apiKey)
    {
        $this->setQueryParam('api_key', $apiKey);
        return $this;
    }

    public function byPermanentId($permId)
    {
        $this->setQueryParam('permid', $permId);

        return $this;
    }

    public function send()
    {
        /**
         * Send auth request
         */
        try {
            $response = parent::send();
        }
        catch(\GoWeb\ClientAPI\Query\Exception\Common $e) {
            $rawResponse = $this->getRawResponse();
            
            if($rawResponse) {
                $response = $rawResponse->json();
            }
            else {
                $response = array(
                    'status' => self::ERROR_GENERIC_SERVER_ERROR,
                    'errorMessage'  => $e->getMessage(),
                );
            }

            $statusExceptionMap = array
            (
                self::ERROR_GENERIC_SERVER_ERROR                    => 'GenericServerError',
                self::ERROR_WRONG_CREDENTIALS                       => 'WrongCredentials',
                self::ERROR_ACCOUNT_BLOCKED                         => 'AccountBlocked',
                self::ERROR_EMAIL_NOT_CONFIRMED                     => 'EmailNotConfirmed',
                self::ERROR_CLIENT_VERSION_NOT_SUPPORTED            => 'ClientVersionNotSupported',
                self::ERROR_NO_ACTIVE_SERVICES_FOUND                => 'NoActiveServicesFound',
            );
            
            // throw generic exception
            if(!isset($statusExceptionMap[$response['status']])) {
                throw new Auth\Exception('Unknown server error with status code : ' . json_encode($response)  );
            }

            // throw defined exception
            $exceptionClass = '\GoWeb\ClientAPI\Query\Auth\Exception\\' . $statusExceptionMap[$response['status']];
            throw new $exceptionClass($response['errorMessage']);
        }

        /**
         * Set active user
         */
        $this->getFactory()->setActiveUser($response->getStructure());

        return $response;
    }

}
