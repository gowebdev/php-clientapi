<?php

namespace GoWeb\ClientAPI\Query;

class RestorePassword extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'users/requestpassword';
    
    protected $_action = self::ACTION_READ;
    
    const REQUEST_PASSWORD_OK           = 0;
    const REQUEST_PASSWORD_WRONG_EMAIL  = 1;
    
    public function setEmail($email)
    {
        $this->setParam('email', $email);
        return $this;
    }
    
    public function send()
    {
        $response = parent::send();
        
        if($response->getParam('status') === self::REQUEST_PASSWORD_WRONG_EMAIL) {
            throw new \Exception('Email not found');
        }
    }
}

