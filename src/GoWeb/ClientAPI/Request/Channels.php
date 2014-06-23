<?php

namespace GoWeb\ClientAPI\Request;

class Channels extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/channels/list';
    
    protected $_structureClassName = '\GoWeb\Api\Model\Media\ChannelList'; 
    
    protected $_authRequired = true;
    
    public function onlyWithTorrent()
    {
        $this->_query['torrent'] = 1;
        
        return $this;
    }
    
    public function sort($field, $descendent = true)
    {
        if(empty($this->_query['sort'])) {
            $this->_query['sort'] = array();
        }
        
        $this->_query['sort'][$field] = $descendent ? -1 : 1;
        
        return $this;
    }
    
    public function orderWithTorrentFirst()
    {
        $this->sort('torrent');
        
        return $this;
    }
    
    public function orderWithTorrentLast()
    {
        $this->sort('torrent', false);
        
        return $this;
    }
    
    public function orderByNumber($desc = false)
    {
        $this->sort('number', $desc);
        
        return $this;
    }
}