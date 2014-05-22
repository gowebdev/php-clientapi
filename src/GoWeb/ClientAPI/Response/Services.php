<?php

namespace GoWeb\ClientAPI\Response;

class Services extends \Sokil\Rest\Transport\Structure
{
    public function getPackets()
    {
        return $this->get('packets');
    }
    
    public function getChannels()
    {
        return $this->get('channels');
    }
}