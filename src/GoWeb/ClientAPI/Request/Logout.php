<?php

namespace GoWeb\ClientAPI\Request;

class Logout extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/users/logout';
}