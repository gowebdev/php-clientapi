<?php

namespace GoWeb\ClientAPI;

class QueryTest extends \Guzzle\Tests\GuzzleTestCase 
{
    protected $_clientAPI;

    public function setUp() {

        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://server.com/1.0/',
            'cacheAdapter' => new CacheAdapterMock,
        ));
        
        // auth
        $client = new \GoWeb\Api\Model\Client(array(
            "error" => 0,
            "permid" => "42e5d49c35880f016ff56f99fd7daa7d262ecee1140347144836574",
            "token" => "0a9373c2de453627ed5abf13e60f9359453984a4",
            "status" => 0,
            "balance" => array(
                "amount" => 995.77,
                "currency" => "EUR"
            ),
            "profile" => array(
                "id" => 36574,
                "email" => "user@example.com",
                "hash" => "c524486gcbb730dcf13d0f5f94655b591dffa32f",
                "last_name" => "Alex",
                "first_name" => "Boldwin",
                "gender" => "MALE",
                "birthday" => "1954-08-11",
                "contract_number" => "00036574",
                "status" => "ACTIVE",
                "tester" => 1
            ),
            "baseServices" => array(
                array(
                    "id" => 44170,
                    "service_id" => 9,
                    "name" => "Full",
                    "custom_name" => "Kitchen",
                    "cost" => 0.033,
                    "total_cost" => 0.033,
                    "status" => "ACTIVE",
                    "catchup" => 1,
                    "ad" => 0,
                    "stb" => array(),
                    "additional" => array(
                        array(
                            "id" => 56294,
                            "service_id" => 11,
                            "name" => "add1",
                            "cost" => 0
                        ),
                        array(
                            "id" => 56295,
                            "service_id" => 12,
                            "name" => "add2",
                            "cost" => 0
                        )
                    )
                )
            ),
            "activeBaseService" => 44170,
            "speed" => array(
                "120KB" => "120",
                "512KB" => "512",
                "1MB" => "1",
                "2MB" => "2",
                "8MB" => "8",
                "36MB" => "36"
            ),
            "rechargePage" => "https://server.com/recharge/methods/userid/38574?theme=empty",
            "profilePage" => "https://server.com/account/dashboard/client_id/36574",
            "time" => 1400879748
        ));
        
        $this->_clientAPI->setActiveUser($client);
    }
    
    public function testSetParam()
    {
        $query = $this->_clientAPI->createRequest('Films');
        
        $query->setQueryParam('query', 'matrix');
        $query->setQueryParam('quality.HD', 1);
        $query->setQueryParam('quality.SD', 0);

        $this->assertEquals(array(
            'query'     => 'matrix',
            'quality'   => array(
                'HD'    => 1,
                'SD'    => 0,
            ),
        ), $query->getQueryParams());
    }
    
    public function testAppendAuthTokenHeader()
    {
        $this->assertTrue($this->_clientAPI->isUserAuthorised());
        
        $this->assertEquals(
            '0a9373c2de453627ed5abf13e60f9359453984a4', 
            $this->_clientAPI->getActiveUser()->getToken()
        );
            
        // test
        $request = $this->_clientAPI->createRequest('Films');
        $request->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(
                200, array(
                    'Content-type' => 'application/json',
                ), json_encode(array(
                    'error' => 0,
                ))
            ),
        )));
        
        $request->send();
        
        $this->assertEquals('0a9373c2de453627ed5abf13e60f9359453984a4', $request->getHeader('X-Auth-Token'));
    }
}
