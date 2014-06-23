<?php 

namespace GoWeb\ClientAPI\Request;

class Films extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/vod/feed';
    
    protected $_structureClassName = '\GoWeb\Api\Model\Media\FilmList';
    
    protected $_authRequired = true;
    
    public function byId($id)
    {
        if(is_array($id)) {
            $this->setQueryParam('id', implode(',', $id) ); 
        } else {
            $this->setQueryParam('id', $id ); 
        }

        return $this;
    }
    
    public function byCategory($categoryID)
    {
        $this->setQueryParam('category', $categoryID);
        
        return $this;
    }
    
    public function byGenre($genre)
    {
        if(is_array($genre))
        {
            $genre = implode(',', $genre);
        }
        
        $this->setQueryParam('genre', $genre);

        return $this;
    }
    
    public function withText($text)
    {
        $this->setQueryParam('query', $text);
        $this->removeQueryParam('name');
        
        return $this;
    }
    
    public function byName($name)
    {
        if($this->getQueryParam('query')) {
            throw new \Exception('Name can not be specified because query param already passed');
        }
        
        $this->setQueryParam('name', $name);
        
        return $this;
    }
    
    public function onlyHD()
    {
        $this->setQueryParam('quality.HD', 1);
        $this->setQueryParam('quality.SD', 0);
        
        return $this;
    }
    
    public function onlySD()
    {
        $this->setQueryParam('quality.HD', 0);
        $this->setQueryParam('quality.SD', 1);
        
        return $this;
    }
    
    public function offset( $offset )
    {
        $this->setQueryParam('offset', $offset);
        
        return $this;
    }
    
    public function length( $length )
    {
        $this->setQueryParam('length', $length);
        
        return $this;
    }
    
    public function sort($field, $descendent = true)
    {        
        $this->setQueryParam('sort.' . $field, $descendent ? -1 : 1);
        
        return $this;
    }
    
    public function orderByName($descendent = true)
    {
        return $this->sort('name', $descendent);
    }
    
    public function orderWithTorrentFirst()
    {
        $this->sort('torrent', false);
        
        return $this;
    }
    public function orderWithoutTorrentFirst()
    {
        $this->sort('torrent');
        
        return $this;
    }
    
}