<?php

namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkVodFeed() {
        $this->_checkVodFeed();
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

    public function testVodFeedValid()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // vod feed
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "total_items" => 200,
                "items" => [
                    [
                        "id" => 1,
                        "name" => "Из Парижа с любовью",
                        "description" => "Примерного сотрудника американского посольства...",
                        "hd" => 1,
                        "year" => 2010,
                        "category" => 1,
                        "director" => "Пьер Морел",
                        "actors" => "Джон Траволта, Джонатан Рис-Майерс, Касия Смутняк...",
                        "duration" => 92,
                        "url" => "http://ivf.goweb.com/apt/ba.mp4?sign=yQqhDoGyXybw-hIdPgtchg&exp=1427454471",
                        "torrent:" => "http://server.com/path-to.torrent",
                        "filesize" => 6584893674,
                        "thumb" => "http://iptv.dev.telehouse-ua.net/screenshots/000/003/380.jpg",
                        "ad" => []
                    ]
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkVodFeed();
        $this->assertNull($validator->getReport()['/vod/feed']);
    }

    public function testVodFeedRequiredFields()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // empty response
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
            ])),

            // empty items
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "total_items" => 200,
                "items" => []
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // empty response
        $validator->checkVodFeed();

        // empty items
        $validator->checkVodFeed();

        $this->assertEquals([
            "total_items"       => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items"             => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.id"          => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.name"        => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.description" => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.category"    => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.filesize"    => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.thumb"       => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "items.ad"          => [$validator::ERROR_TYPE_FIELD_REQUIRED],
        ], $validator->getReport()['/vod/feed']);
    }

    public function testVodFeedFieldsType()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // wrong types globals
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                    "error" => 0,
                    "total_items" => "",
                    "items" => ""
            ])),

            // wrong types in item dictionary
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "total_items" => 200,
                "items" => [
                    [
                        "id" => "id",
                        "name" => 1,
                        "description" => 1,
                        "hd" => "hd",
                        "year" => "year",
                        "rate" => "rate",
                        "category" => "cat",
                        "genres" => "",
                        "director" => 1,
                        "actors" => 1,
                        "country" => 1,
                        "duration" => "duration",
                        "url" => 1,
                        "torrent" => 1,
                        "hlts1" => 1,
                        "filesize" => "5Mb",
                        "thumb" => 1,
                        "ad" => 1
                    ], [
                        "id" => "1",
                        "name" => "Из Парижа с любовью",
                        "description" => "Примерного сотрудника американского посольства...",
                        "hd" => "2",
                        "year" => "2010",
                        "rate" => "6",
                        "category" => "1",
                        "director" => "Пьер Морел",
                        "actors" => "Джон Траволта, Джонатан Рис-Майерс, Касия Смутняк...",
                        "duration" => "92",
                        "url" => "http://ivf.goweb.com/apt/ba.mp4?sign=yQqhDoGyXybw-hIdPgtchg&exp=1427454471",
                        "torrent" => "http://server.com/path-to.torrent",
                        "filesize" => 6584893674,
                        "thumb" => "http://iptv.dev.telehouse-ua.net/screenshots/000/003/380.jpg",
                        "ad" => []
                    ],
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // wrong types globals
        $validator->checkVodFeed();

        // wrong types in item dictionary
        $validator->checkVodFeed();

        $this->assertEquals([
            "total_items"       => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "items"             => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "items.id"          => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "items.name"        => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.description" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.hd"          => [$validator::ERROR_TYPE_FIELD_MUSTBEINT, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "items.year"        => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "items.rate"        => [$validator::ERROR_TYPE_FIELD_MUSTBEINT, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "items.category"    => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "items.genres"      => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "items.director"    => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.actors"      => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.country"     => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.duration"    => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "items.url"         => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.torrent"     => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.hlts1"       => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.filesize"    => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "items.thumb"       => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "items.ad"          => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING, $validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
        ], $validator->getReport()['/vod/feed']);
    }
}
