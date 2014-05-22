<?php

namespace GoWeb\ClientAPI\Query;

class Logout extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/users/logout';
    
    protected $_action = self::ACTION_READ;
}