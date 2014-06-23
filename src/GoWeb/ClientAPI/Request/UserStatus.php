<?php 

namespace GoWeb\ClientAPI\Request;

class UserStatus extends \Sokil\Rest\Client\Request
{
    protected $_url = '/users/status';
    
    protected $_action = self::ACTION_READ;
    
    protected $_authRequired = true;
    
}