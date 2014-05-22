<?php 

namespace GoWeb\ClientAPI\Query;

class UserStatus extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/users/status';
    
    protected $_action = self::ACTION_READ;
}