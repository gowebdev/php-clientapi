<?php

namespace GoWeb\ClientAPI;

class QueryTest extends \Guzzle\Tests\GuzzleTestCase 
{

    protected $_clientAPI;
    protected $_query;

    public function setUp() {

        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'https://tvapi.goweb.com/1.0/',
            'cacheAdapter' => new CacheAdapterMock,
        ));
        
        $this->_clientAPI->setDemoCredentials()->auth()->send();
    }

    public function testGetCachedWithModificationChecking_NotModified() {

        /**
         * Request model
         */
        $query = $this->_clientAPI->query('Films');
        $model = $query->send();

        $this->assertEquals('MISS from GuzzleCache', $query->getRawResponse()->getHeader('X-Cache'));
        
        $this->assertNotEmpty($model->get('total_items'));

        /**
         * Request from cache
         */
        $query = $this->_clientAPI->query('Films');
        $model = $query->send();

        $this->assertEquals('HIT from GuzzleCache', $query->getRawResponse()->getHeader('X-Cache'));
        $this->assertNotEmpty($model->get('total_items'));
    }
    
    public function testSetParam()
    {
        $query = $this->_clientAPI->query('Films');
        
        $query->setParam('query', 'matrix');
        $query->setParam('quality.HD', 1);
        $query->setParam('quality.SD', 0);

        $this->assertEquals(array(
            'query'     => 'matrix',
            'quality'   => array(
                'HD'    => 1,
                'SD'    => 0,
            ),
        ), $query->toArray());
    }
}
