<?php 

namespace GoWeb\ClientAPI\Request;

class CreateFavouriteChannel extends \Sokil\Rest\Client\Request\CreateRequest
{
    protected $_url = '/channels/favourite';
    
    public function channel($channelId)
    {
        $this->setQueryParam('channel', $channelId );
        
        return $this;
    }
}