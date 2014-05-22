<?php

namespace GoWeb\ClientAPI\Query;

class MetaTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testSend()
    {
        $response = array(
            "error" => 0,
            "name" => "GoWeb",
            "description" => array(
                "uk" => "P2P television",
                "en" => "P2P television",
                "ru" => "P2P television",
                "be" => "P2P television",
            ),
            "icon" => "http:\/\/tvapi.goweb.com\/img\/logo\/goweb.png",
            "time" => 1400760359
        );
        
        $clientApi = new \GoWeb\ClientAPI('http://api.mw/1.0');
        $clientApi->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($response)),
        )));
        
        $meta = $clientApi->query('Meta')->send();
        
        $this->assertEquals($response, $meta->toArray());
    }
}