<?php

namespace GoWeb\ClientAPI\Request;

class Meta extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/';
    
    protected $_structureClassName = '\GoWeb\ClientAPI\Response\Meta';
}

