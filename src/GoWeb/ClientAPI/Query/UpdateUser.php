<?php

namespace GoWeb\ClientAPI\Query;

class UpdateUser extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/user';
    
    protected $_action = self::ACTION_UPDATE;
    
    public function setUser(\GoWeb\Api\Model\Client $client)
    {
        $this
            ->setParam('email', $client->getEmail())
            ->setParam('password', $client->getProfile()->getPassword())
            ->setParam('last_name', $client->getLastName())
            ->setParam('first_name', $client->getFirstName())
            ->setParam('gender', $client->getGender())
            ->setParam('birthday', $client->getBirthday());
        
        return $this;
    }
}

