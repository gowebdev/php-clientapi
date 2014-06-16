<?php

namespace GoWeb\ClientAPI\Request;

class AuthTest extends \Guzzle\Tests\GuzzleTestCase
{    
    public function testAutoAuthWithDemoCredentials()
    {
        $demoClient = array(
            "error" => 0,
            "token" => "5440644e27a3259da3feaab416f37cee06d12569",
            "status" => 0,
            "balance" => array(
                "amount" => 0,
                "currency" => "EUR"
            ),
            "profile" => array(
                "id" => 0,
                "first_name" => "Гість",
                "last_name" => "",
                "contract_number" => 0,
                "status" => "ACTIVE"
            ),
            "baseServices" => [
                array(
                    "id" => 0,
                    "service_id" => 6,
                    "name" => "Рекламний",
                    "cost" => 0,
                    "catchup" => 0,
                    "ad" => 1,
                    "total_cost" => 0
                )
            ],
            "speed" => array(
                "120KB" => "120 килобит в секунду",
                "512KB" => "512 килобит в секунду",
                "1MB" => "1 мегабит в секунду",
                "2MB" => "2 мегабита в секунду",
                "8MB" => "8 мегабит в секунду",
                "36MB" => "36 мегабит в секунду"
            ),
            "time" => 1402652985
        );
        
        $clientApi = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://apiserver.com/1.0',
            'cacheAdapter'  => new \GoWeb\ClientAPI\CacheAdapterMock,
        ));
        
        // mock response
        $clientApi->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($demoClient)),
            // meta
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(array('error' => 0))),
        )));
        
        $this->assertEmpty($clientApi->getActiveUser());
        
        $films = $clientApi->createRequest('Films')->send();
        
        $this->assertInstanceOf('\GoWeb\Api\Model\Client', $clientApi->getActiveUser());
        $this->assertEquals(0, $clientApi->getActiveUser()->getId());
        $this->assertEquals('Гість', $clientApi->getActiveUser()->getFirstName());
    }
    
    /**
     * @expectedException \GoWeb\ClientAPI\Request\Auth\Exception\WrongCredentials
     */
    public function testAuthWithWrongCredentials()
    {
        $clientApi = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://apiserver.com/1.0',
            'cacheAdapter'  => new \GoWeb\ClientAPI\CacheAdapterMock,
        ));
        
        $clientApi->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(200, [
                'Content-type' => 'application/json',
            ], json_encode(array(
                "error" => 1,
                "errorMessage" => "Невірний E-mail чи пароль",
                "status" => 2,
                "time" => 1402653115,
            ))),
        )));
        
        $this->assertEmpty($clientApi->getActiveUser());
        
        // set wrong credentials
        $clientApi->setCredentials('foo@bar.com', 'foobar');
        
        $films = $clientApi->createRequest('Films')->send();
    }
}