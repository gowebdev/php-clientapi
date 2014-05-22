<?php

namespace GoWeb\ClientAPI\Query;

class MetaTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testSend()
    {
        $clientApi = new \GoWeb\ClientAPI('http://api.mw/1.0');
        $clientApi->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(array(
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
            ))),
        )));
        
        $meta = $clientApi->query('Meta')->send();
        
        var_dump($meta->toArray());
    }
}