<?php

namespace GoWeb\ClientAPI\Request;

class Services extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/services';
    
    protected $_structureClassName = '\GoWeb\ClientAPI\Response\Services';
}

