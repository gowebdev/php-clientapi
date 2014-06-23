<?php 

namespace GoWeb\ClientAPI\Request;

class DeleteFavouriteChannel extends \Sokil\Rest\Client\Request\DeleteRequest
{
    protected $_url = '/channels/favourite';
    
    protected $_authRequired = true;
    
    public function channel($channelId)
    {
        $this->setQueryParam('channel', $channelId );
        
        return $this;
    }
}