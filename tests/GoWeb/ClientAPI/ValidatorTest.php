<?php

namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkChannelsList() {
        $this->_checkChannelsList();
    }
}

class ValidatorTest extends \Guzzle\Tests\GuzzleTestCase 
{
    protected $_clientAPI;

    protected $_demoUser;

    public function setUp()
    {
        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://dkjglkdsfjgkldsfjgkldsfjglkdsfjglkdfsgjldksfjgkldfg.com/1.0/',
            'cacheAdapter' => new CacheAdapterMock,
        ));

        $this->_demoUser =  array(
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
    }

    /**
     * @expectedException \GoWeb\ClientAPI\Validator\Exception\UnknownHost
     */
    public function testSetUnexistedHost()
    {
        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://dkjglkdsfjgkldsfjgkldsfjglkdsfjglkdfsgjldksfjgkldfg.com/1.0/',
            'cacheAdapter' => new CacheAdapterMock,
        ));
        
        $validator = $this->_clientAPI->getValidator();
        $validator->isValid();
    }

    public function testValidChannelList()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // channel list
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "channels" => [
                    [
                        "name" => "Tvi",
                        "url" => "http://example.com/stream?exp=1349261192&sign=d41d8cd98f0",
                        "torrent" => "http://server.com/path-to.torrent",
                        "logo" => "http://server.com/logo.png",
                        "genre" => "News and informational",
                        "genre_id" => "0",
                        "channel_id" => "1",
                        "hd" => "1",
                        "3d" => "1",
                        "fav" => "1",
                        "ad" => ""
                    ], [
                        "name" => "Tvi2",
                        "url" => "http://example.com/stream?exp=1349261192&sign=d41d8cd98f0",
                        "torrent" => "http://server.com/path-to.torrent",
                        "logo" => "http://server.com/logo.png",
                        "genre" => "News and informational",
                        "genre_id" => 2,
                        "channel_id" => 2,
                        "hd" => 0,
                        "3d" => 0,
                        "fav" => 0,
                        "ad" => []
                    ]
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkChannelsList();
        $this->assertNull($validator->getReport()['/channels/list']);
    }

    public function testChannelListRequiredFields()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // globals
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(["error" => 0])),

            // channel dictionary
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "channels" => [
                    ["channel1"],
                    ["channel2"],
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // globals
        $validator->checkChannelsList();

        //channel dictionary
        $validator->checkChannelsList();

        $this->assertEquals([
            "channels"            => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "channels.name"       => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "channels.url"        => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "channels.genre"      => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "channels.genre_id"   => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "channels.channel_id" => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "channels.ad"         => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
        ],$validator->getReport()['/channels/list']);
    }

    public function testChannelListFieldTypes()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // channel list
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "channels" => [
                    // must be array
                    "channel1",

                    // wrong filed type
                    [
                        "name" => 0,
                        "url" => 1,
                        "torrent" => 0,
                        "hlts1" => 1,
                        "genre" => [],
                        "genre_id" => "genre_id",
                        "channel_id" => "channel_id",
                        "logo" => 1,
                        "hd" => "hd",
                        "3d" => "3d",
                        "fav" => "fav",
                        "ad" => 1
                    ],

                    // out of range
                    [
                        "name" => "Tvi",
                        "url" => "http://example.com/stream?exp=1349261192&sign=d41d8cd98f0",
                        "torrent" => "http://server.com/path-to.torrent",
                        "hlts1" => "1",
                        "genre" => "News and informational",
                        "genre_id" => "1",
                        "channel_id" => "1",
                        "logo" => "http://server.com/logo.png",
                        "hd" => 2,
                        "3d" => 2,
                        "fav" => 2,
                        "ad" => ""
                    ],

                    // field "ad" can be string or array
                    [
                        "name" => "Tvi",
                        "url" => "http://example.com/stream?exp=1349261192&sign=d41d8cd98f0",
                        "torrent" => "http://server.com/path-to.torrent",
                        "hlts1" => "1",
                        "genre" => "News and informational",
                        "genre_id" => 1,
                        "channel_id" => 1,
                        "logo" => "http://server.com/logo.png",
                        "hd" => "1",
                        "3d" => "1",
                        "fav" => "1",
                        "ad" => []
                    ]
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkChannelsList();

        $this->assertEquals([
            "channels"            => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "channels.name"       => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "channels.url"        => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "channels.torrent"    => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "channels.hlts1"      => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "channels.genre"      => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "channels.genre_id"   => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "channels.channel_id" => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "channels.logo"       => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "channels.hd"         => [$validator::ERROR_TYPE_FIELD_MUSTBEINT, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "channels.3d"         => [$validator::ERROR_TYPE_FIELD_MUSTBEINT, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "channels.fav"        => [$validator::ERROR_TYPE_FIELD_MUSTBEINT, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "channels.ad"         => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING, $validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
        ], $validator->getReport()['/channels/list']);
    }
}
