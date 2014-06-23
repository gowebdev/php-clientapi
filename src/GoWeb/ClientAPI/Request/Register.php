<?php

namespace GoWeb\ClientAPI\Request;

class Register extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/users/register';
    
    protected $_authRequired = true;
    
    const ERROR_NONE                                    = 0;
    const ERROR_REQUIRED_FIELDS_EMPTY                   = 1;
    const ERROR_EMAIL_ALREADY_REGISTERED                = 2;
    const ERROR_INVALID_EMAIL                           = 3;
    const ERROR_INVALID_PASSWORD                        = 4;
    const ERROR_GENERIC_VALIDATION_ERROR                = 5;

    public function init()
    {
        // define client
        if($this->getFactory()->getAgent()) {
            $this->setAgent($this->getFactory()->getAgent());
        }
    }


    public function setEmail( $email )
    {
        $this->setQueryParam( 'email', $email );

        return $this;
    }

    public function setPassword( $password )
    {
        $this->setQueryParam( 'password', $password );

        return $this;
    }
    
    public function setAPIKey($apiKey)
    {
        $this->setQueryParam('api_key', $apiKey);
        return $this;
    }
    
    public function setAgent($agent)
    {
        $this->setQueryParam('agent', $agent);
        return $this;
    }

    public function send()
    {
        try
        {
            $newUserData = parent::send();
        }
        catch(\GoWeb\ClientAPI\Request\Exception\Common $e)
        {
            $response = $this->getRawResponse()->json();

            $statusExceptionMap = array
            (
                self::ERROR_REQUIRED_FIELDS_EMPTY       => 'RequiredFieldsEmpty',
                self::ERROR_EMAIL_ALREADY_REGISTERED    => 'EmailAlreadyRegistered',
                self::ERROR_INVALID_EMAIL               => 'InvalidEmail',
                self::ERROR_INVALID_PASSWORD            => 'InvalidPassword',
                self::ERROR_GENERIC_VALIDATION_ERROR    => 'GenericValidationError'
            );

            // throw generic exception
            if(!isset($statusExceptionMap[$response['status']])) {
                throw new Register\Exception('Unknown server error with status code : ' . $response['status']  );
            }
            
            // throw defined exception
            $exceptionClass = '\GoWeb\ClientAPI\Request\Register\Exception\\' . $statusExceptionMap[$response['status']];
            throw new $exceptionClass($response['errorMessage']);
        }

        return $newUserData;
    }

}
