<?php

namespace GoWeb\ClientAPI\Query;

class AuthTest extends \Guzzle\Tests\GuzzleTestCase
{    
    public function testAutoAuthWithDemoCredentials()
    {
        $clientApi = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'https://tvapi.goweb.com/1.0',
            'cacheAdapter'  => new \GoWeb\ClientAPI\CacheAdapterMock,
        ));
        
        $this->assertEmpty($clientApi->getActiveUser());
        
        $films = $clientApi->createRequest('Films')->send();
        
        $this->assertInstanceOf('\GoWeb\Api\Model\Client', $clientApi->getActiveUser());
        $this->assertEquals(0, $clientApi->getActiveUser()->getId());
        $this->assertEquals('Guest', $clientApi->getActiveUser()->getFirstName());
    }
    
    /**
     * @expectedException \GoWeb\ClientAPI\Query\Auth\Exception\WrongCredentials
     */
    public function testAuthWithWrongCredentials()
    {
        $clientApi = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'https://tvapi.goweb.com/1.0',
            'cacheAdapter'  => new \GoWeb\ClientAPI\CacheAdapterMock,
        ));
        
        $this->assertEmpty($clientApi->getActiveUser());
        
        // set wrong credentials
        $clientApi->setCredentials('foo@bar.com', 'foobar');
        
        $films = $clientApi->createRequest('Films')->send();
    }
}