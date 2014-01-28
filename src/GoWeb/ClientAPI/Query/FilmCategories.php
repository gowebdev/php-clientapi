<?php 

namespace GoWeb\ClientAPI\Query;

class FilmCategories extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'vod/genres';

    protected $_responseModel = '\GoWeb\Api\Model\Media\FilmCategories';
    
    protected $_cache = true;
}