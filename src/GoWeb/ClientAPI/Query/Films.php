<?php 

namespace GoWeb\ClientAPI\Query;

class Films extends \GoWeb\ClientAPI\Query
{
    protected $_url = '/vod/feed';

    protected $_action = self::ACTION_READ;
    
    protected $_structureClassName = '\GoWeb\Api\Model\Media\FilmList';
    
    public function byId($id)
    {
        if(is_array($id)) {
            $this->setParam('id', implode(',', $id) ); 
        } else {
            $this->setParam('id', $id ); 
        }

        return $this;
    }
    
    public function byCategory($categoryID)
    {
        $this->setParam('category', $categoryID);
        
        return $this;
    }
    
    public function byGenre($genre)
    {
        if(is_array($genre))
        {
            $genre = implode(',', $genre);
        }
        
        $this->setParam('genre', $genre);

        return $this;
    }
    
    public function withText($text)
    {
        $this->setParam('query', $text);
        $this->removeParam('name');
        
        return $this;
    }
    
    public function byName($name)
    {
        if($this->getParam('query')) {
            throw new \Exception('Name can not be specified because query param already passed');
        }
        
        $this->setParam('name', $name);
        
        return $this;
    }
    
    public function onlyHD()
    {
        $this->setParam('quality.HD', 1);
        $this->setParam('quality.SD', 0);
        
        return $this;
    }
    
    public function onlySD()
    {
        $this->setParam('quality.HD', 0);
        $this->setParam('quality.SD', 1);
        
        return $this;
    }
    
    public function offset( $offset )
    {
        $this->setParam('offset', $offset);
        
        return $this;
    }
    
    public function length( $length )
    {
        $this->setParam('length', $length);
        
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