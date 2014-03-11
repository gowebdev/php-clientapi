<?php

namespace GoWeb\ClientAPI\Query;

class Channels extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'channels/list';
    
    protected $_responseModelClassname = '\GoWeb\Api\Model\Media\ChannelList'; 
    
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