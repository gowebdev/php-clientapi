<?php

namespace GoWeb\ClientAPI\Request;

class RestorePassword extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/users/requestpassword';
    
    const REQUEST_PASSWORD_OK           = 0;
    const REQUEST_PASSWORD_WRONG_EMAIL  = 1;
    
    public function setEmail($email)
    {
        $this->setQueryParam('email', $email);
        return $this;
    }
    
    public function send()
    {
        $response = parent::send();
        
        if($response->get('status') === self::REQUEST_PASSWORD_WRONG_EMAIL) {
            throw new \Exception('Email not found');
        }
    }
}

