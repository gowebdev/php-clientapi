<?php

namespace GoWeb\ClientAPI\Request;

class UpdateUser extends \Sokil\Rest\Client\Request\UpdateRequest
{
    protected $_url = '/user';
    
    public function setUser(\GoWeb\Api\Model\Client $client)
    {
        $this
            ->setQueryParam('email', $client->getEmail())
            ->setQueryParam('password', $client->getProfile()->getPassword())
            ->setQueryParam('last_name', $client->getLastName())
            ->setQueryParam('first_name', $client->getFirstName())
            ->setQueryParam('gender', $client->getGender())
            ->setQueryParam('birthday', $client->getBirthday());
        
        return $this;
    }
}

