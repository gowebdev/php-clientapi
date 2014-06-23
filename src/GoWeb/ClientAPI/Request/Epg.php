<?php

namespace GoWeb\ClientAPI\Request;

class Epg extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/channels/epg';
    
    protected $_structureClassName = '\GoWeb\Api\Model\Media\ChannelPrograms';
    
    protected $_authRequired = true;
    
    public function forChannel($channel)
    {
        $this->setQueryParam('channel_id', $channel);
        
        return $this;
    }
    
    public function fromTime( $time )
    {
        $this->setQueryParam('time_from', $time);
        
        return $this;
    }
    
    public function toTime( $time )
    {
        $this->setQueryParam('time_to', $time);
        
        return $this;
    }
}