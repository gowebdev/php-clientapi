<?php

namespace GoWeb;

class ClientAPITest extends \PHPUnit_Framework_TestCase
{
    public function testOnBeforeSend()
    {
        // configure client api
        $clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://server.com/1.0/'
        ));
        
        $status = new \stdClass;
        $status->ok = 0;
        
        $clientAPI->onBeforeSend(function() use($status) {
            $status->ok++;
        });
        
        $request = $clientAPI->createRequest('Films');
        $request->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(
                200, array(
                    'Content-type' => 'application/json',
                ), json_encode(array(
                    'error' => 0,
                ))
            ),
            // films
            new \Guzzle\Http\Message\Response(
                200, array(
                    'Content-type' => 'application/json',
                ), json_encode(array(
                    'error' => 0,
                ))
            ),
        )));
        
        $request->send();
        
        $this->assertEquals(2, $status->ok);
    }
}