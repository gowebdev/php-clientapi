<?php 

namespace GoWeb\ClientAPI\Query;

class DeleteFavouriteChannel extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/channels/favourite';
    
    protected $_action = self::ACTION_DELETE;
    
    public function channel($channelId)
    {
        $this->setParam('channel', $channelId );
        
        return $this;
    }
}