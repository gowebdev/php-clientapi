<?php

namespace GoWeb\ClientAPI\Request;

class FilmsTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testSort()
    {
        $clientApi = new \GoWeb\ClientAPI('http://api.mw/1.0');
        
        $request = $clientApi->createRequest('Films')
            ->sort('field1', false)
            ->sort('field2', true);
        
        $this->assertEquals(array(
            'sort' => array(
                'field1'    => 1,
                'field2'    => -1,
            )
        ), $request->getQueryParams());
        
        $this->assertEquals('http://api.mw/1.0/vod/feed?sort%5Bfield1%5D=1&sort%5Bfield2%5D=-1', $request->getUrl());
    }
}