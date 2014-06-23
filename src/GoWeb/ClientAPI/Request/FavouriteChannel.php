<?php 

namespace GoWeb\ClientAPI\Request;

class FavouriteChannel extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/channels/favourite';
    
    protected $_authRequired = true;
    
    public function channel($channelId)
    {
        $this->setQueryParam('channel', $channelId );
        
        return $this;
    }
}