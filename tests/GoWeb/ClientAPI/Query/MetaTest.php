<?php

namespace GoWeb\ClientAPI\Request;

class MetaTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testSend()
    {
        $response = array(
            "error" => 0,
            "name" => "GoWeb",
            "description" => array(
                "uk" => "P2P television UK",
                "en" => "P2P television EN",
                "be" => "P2P television BE",
                "ru" => "P2P television RU",
            ),
            "icon" => "http:\/\/server.com\/img\/logo\/goweb.png",
            "time" => 1400760359
        );
        
        $clientApi = new \GoWeb\ClientAPI('http://example.com/1.0');
        $clientApi->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($response)),
        )));
        
        /* @var $meta \GoWeb\ClientAPI\Response\Meta */
        $request = $clientApi->createRequest('Meta');
        $meta = $request->send();
        
        $this->assertEquals($response['name'], $meta->getName());
        $this->assertEquals($response['description'], $meta->getDescription());
        $this->assertEquals($response['description']['uk'], $meta->getDescription('uk'));
        $this->assertEquals($response['icon'], $meta->getIcon());
    }
}