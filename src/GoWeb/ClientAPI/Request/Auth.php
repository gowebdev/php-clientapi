<?php

namespace GoWeb\ClientAPI\Request;

class Auth extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/users/authorize';
    
    protected $_structureClassName = 'GoWeb\Api\Model\Client';

    const ERROR_NONE                                    = 0;
    const ERROR_GENERIC_SERVER_ERROR                    = 1;
    const ERROR_WRONG_CREDENTIALS                       = 2;
    const ERROR_ACCOUNT_BLOCKED                         = 3;
    const ERROR_EMAIL_NOT_CONFIRMED                     = 4;
    const ERROR_CLIENT_VERSION_NOT_SUPPORTED            = 5;
    const ERROR_NO_ACTIVE_SERVICES_FOUND                = 6;
    
    public function init()
    {
        // this event handler overrides defined in factory
        $this->onParseResponse(function(\Guzzle\Common\Event $event) {
            $event->stopPropagation();
            
            $response = $event['response'];
            
            if($response->error === 0) {
                $this->getFactory()->setActiveUser($response->getStructure());
                return;
            }

            $statusExceptionMap = array(
                self::ERROR_GENERIC_SERVER_ERROR                    => 'GenericServerError',
                self::ERROR_WRONG_CREDENTIALS                       => 'WrongCredentials',
                self::ERROR_ACCOUNT_BLOCKED                         => 'AccountBlocked',
                self::ERROR_EMAIL_NOT_CONFIRMED                     => 'EmailNotConfirmed',
                self::ERROR_CLIENT_VERSION_NOT_SUPPORTED            => 'ClientVersionNotSupported',
                self::ERROR_NO_ACTIVE_SERVICES_FOUND                => 'NoActiveServicesFound',
            );

            // throw generic exception
            if(!isset($statusExceptionMap[$response->status])) {
                throw new Auth\Exception('Unknown server error with status code ' . $response->status . ' : ' . $response);
            }

            // throw defined exception
            $exceptionClass = '\GoWeb\ClientAPI\Request\Auth\Exception\\' . $statusExceptionMap[$response->status];
            throw new $exceptionClass($response->errorMessage);
        }, 10000);
    }
    
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

}
