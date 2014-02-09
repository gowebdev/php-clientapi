<?php 

namespace GoWeb\ClientAPI\Query;

class FilmCategories extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'vod/genres';

    protected $_responseModelClassname = '\GoWeb\Api\Model\Media\FilmCategories';
    
    protected $_revalidate = self::REVALIDATE_SKIP;
}