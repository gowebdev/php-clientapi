<?php

namespace GoWeb\ClientAPI\Query;

class Epg extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/channels/epg';
    
    protected $_action = self::ACTION_READ;
    
    protected $_structureClassName = '\GoWeb\Api\Model\Media\ChannelPrograms';
    
    public function forChannel($channel)
    {
        $this->setParam('channel_id', $channel);
        
        return $this;
    }
    
    public function fromTime( $time )
    {
        $this->setParam('time_from', $time);
        
        return $this;
    }
    
    public function toTime( $time )
    {
        $this->setParam('time_to', $time);
        
        return $this;
    }
}