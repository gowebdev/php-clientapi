<?php

namespace GoWeb\ClientAPI\Query;

class Meta extends \Sokil\Rest\Client\Request
{
    protected $_url = '/';
    
    protected $_action = self::ACTION_READ;
}

