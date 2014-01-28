<?php

namespace GoWeb\ClientAPI\Query;

class Register extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'users/register';

    const ERROR_NONE                                    = 0;
    const ERROR_REQUIRED_FIELDS_EMPTY                   = 1;
    const ERROR_EMAIL_ALREADY_REGISTERED                = 2;
    const ERROR_INVALID_EMAIL                           = 3;
    const ERROR_INVALID_PASSWORD                        = 4;
    const ERROR_GENERIC_VALIDATION_ERROR                = 5;

    public function setEmail( $email )
    {
        $this->setParam( 'email', $email );

        return $this;
    }

    public function setPassword( $password )
    {
        $this->setParam( 'password', $password );

        return $this;
    }

    public function send()
    {
        try
        {
            $newUserData = parent::send();
        }
        catch(\GoWeb\ClientAPI\Query\Exception\Common $e)
        {
            $rawResponse = $this->getRawResponse();

            $statusExceptionMap = array
            (
                self::ERROR_REQUIRED_FIELDS_EMPTY       => 'RequiredFieldsEmpty',
                self::ERROR_EMAIL_ALREADY_REGISTERED    => 'EmailAlreadyRegistered',
                self::ERROR_INVALID_EMAIL               => 'InvalidEmail',
                self::ERROR_INVALID_PASSWORD            => 'InvalidPassword',
                self::ERROR_GENERIC_VALIDATION_ERROR    => 'GenericValidationError'
            );

            // throw generic exception
            if(!isset($statusExceptionMap[$rawResponse['status']]))
                throw new Register\Exception('Unknown server error with status code : ' . $rawResponse['status']  );

            // throw defined exception
            $exceptionClass = '\GoWeb\ClientAPI\Query\Register\Exception\\' . $statusExceptionMap[$rawResponse['status']];
            throw new $exceptionClass($rawResponse['errorMessage']);
        }

        return $newUserData;
    }

}
