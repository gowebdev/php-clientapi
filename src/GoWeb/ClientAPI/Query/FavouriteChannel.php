<?php 

namespace GoWeb\ClientAPI\Query;

class FavouriteChannel extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'channels/favourite';
    
    
    public function channel($channelId)
    {
        $this->setParam('channel', $channelId );
        
        return $this;
    }
}