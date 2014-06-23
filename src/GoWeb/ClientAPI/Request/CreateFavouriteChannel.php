<?php 

namespace GoWeb\ClientAPI\Request;

class CreateFavouriteChannel extends \Sokil\Rest\Client\Request\CreateRequest
{
    protected $_url = '/channels/favourite';
    
    protected $_authRequired = true;
    
    public function channel($channelId)
    {
        $this->setQueryParam('channel', $channelId );
        
        return $this;
    }
}