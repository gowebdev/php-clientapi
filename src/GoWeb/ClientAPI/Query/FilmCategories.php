<?php 

namespace GoWeb\ClientAPI\Query;

class FilmCategories extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'vod/genres';

    protected $_action = self::ACTION_READ;
    
    protected $_responseModelClassname = '\GoWeb\Api\Model\Media\FilmCategories';
}