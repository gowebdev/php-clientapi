<?php

namespace GoWeb\ClientAPI\Query;

class Epg extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'channels/epg';
    
    protected $_responseModelClassname = '\GoWeb\Api\Model\Media\ChannelPrograms';
    
    protected $_revalidate = self::REVALIDATE_SKIP;
    
    // cache for 10 minutes
    protected $_cacheExpire = 600;
    
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