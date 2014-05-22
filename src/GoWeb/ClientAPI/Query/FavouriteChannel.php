<?php 

namespace GoWeb\ClientAPI\Query;

class FavouriteChannel extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/channels/favourite';
    
    protected $_action = self::ACTION_READ;
    
    public function channel($channelId)
    {
        $this->setParam('channel', $channelId );
        
        return $this;
    }
}