<?php 

namespace GoWeb\ClientAPI\Request;

class FavouriteChannel extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/channels/favourite';
    
    public function channel($channelId)
    {
        $this->setQueryParam('channel', $channelId );
        
        return $this;
    }
}