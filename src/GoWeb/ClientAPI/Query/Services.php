<?php

namespace GoWeb\ClientAPI\Query;

class Services extends \Sokil\Rest\Client\Request
{
    protected $_url = '/services';
    
    protected $_action = self::ACTION_READ;
}

