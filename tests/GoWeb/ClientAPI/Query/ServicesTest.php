<?php

namespace GoWeb\ClientAPI\Request;

class ServicesTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testSend()
    {
        $response = array(
            "error" => 0,
            "packets" => array(
                array(
                    "id" => 6,
                    "name" => "Demo",
                    "cost" => "0",
                    "type" => "BASE",
                    "channels" => array(
                        35,
                    )
                ),
                array(
                    "id" => 9,
                    "name" => "Basic",
                    "cost" => "0.99",
                    "type" => "BASE",
                    "channels" => array(
                        35,
                        337,
                    )
                )
            ),
            "channels" => array(
                array(
                    "id" => 35,
                    "name" => "TV1",
                    "logo" => "http://example.com/logos/68x48/bigudi.png"
                ),
                array(
                    "id" => 337,
                    "name" => "TV2",
                    "logo" => "http://example.com/logos/68x48/zero.png"
                ),
            ),
            "time" => 1400770622
        );
        
        $clientApi = new \GoWeb\ClientAPI('http://api.mw/1.0');
        $clientApi->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($response)),
        )));
        
        $meta = $clientApi->createRequest('Services')->send();
        
        $this->assertEquals($response['packets'], $meta->getPackets());
        $this->assertEquals($response['channels'], $meta->getChannels());
    }
}