<?php 

namespace GoWeb\ClientAPI\Request;

class FilmCategories extends \Sokil\Rest\Client\Request\ReadRequest
{
    protected $_url = '/vod/genres';
    
    protected $_structureClassName = '\GoWeb\Api\Model\Media\FilmCategories';
    
    protected $_authRequired = true;
}