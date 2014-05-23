<?php 

namespace GoWeb\ClientAPI\Query;

class CreateFavouriteChannel extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/channels/favourite';
    
    protected $_action = self::ACTION_CREATE;
    
    public function channel($channelId)
    {
        $this->setParam('channel', $channelId );
        
        return $this;
    }
}