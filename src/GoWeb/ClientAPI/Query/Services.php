<?php

namespace GoWeb\ClientAPI\Query;

class Services extends \Sokil\Rest\Client\Request
{
    protected $_url = '/services';
    
    protected $_action = self::ACTION_READ;
    
    protected $_structureClassName = '\GoWeb\ClientAPI\Response\Services';
}

