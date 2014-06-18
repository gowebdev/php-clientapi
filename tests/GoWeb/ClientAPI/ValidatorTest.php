<?php

namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkChannelsEpg() {
        $this->_checkChannelsEpg();
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

     public function testEpgValid()
     {
         // mock response
         $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
             // auth
             new \Guzzle\Http\Message\Response(200, array(
                 'Content-type' => 'application/json',
             ), json_encode($this->_demoUser)),

             // channels epg
             new \Guzzle\Http\Message\Response(200, array(
                 'Content-type' => 'application/json',
             ), json_encode([
                 "error" => 0,
                 "epg" => [
                     "1" => [
                         [
                             "name" => "Muñeca brava",
                             "from" => 1349341914,
                             "to" => 1349342312,
                             "url" => "http://example.com/tvodfile?exp=1349261192&sign=d41d8cd98f0",
                             "torrent" => "http://example.com/torrent"
                         ], [
                             "name" => "Santa Barbara",
                             "from" => 1349341914,
                             "to" => 1349342312,
                             "url" => "http://example.com/tvodfile?exp=1349261192&sign=d41d8cd98f0",
                             "torrent" => "http://example.com/torrent"
                         ]

                     ],
                     "2" => [
                         [
                             "name" => "Star Trek",
                             "from" => 1349341914,
                             "to" => 1349342312,
                             "url" => "http://example.com/tvodfile?exp=1349261192&sign=d41d8cd98f0",
                             "torrent" => "http://example.com/torrent"
                         ]
                     ]
                 ]
             ])),
         )));

         $validator = new ValidatorWrapper($this->_clientAPI);
         $validator->checkChannelsEpg();
         $this->assertNull($validator->getReport()['/channels/epg']);
    }


    /**
     * @expectedException \Exception
     */
    public function testEpgNotExists()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // required epg
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkChannelsEpg();
    }

     public function testEpgRequiredFields()
     {
         // mock response
         $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
             // auth
             new \Guzzle\Http\Message\Response(200, array(
                 'Content-type' => 'application/json',
             ), json_encode($this->_demoUser)),

             // required dictionary fields
             new \Guzzle\Http\Message\Response(200, array(
                 'Content-type' => 'application/json',
             ), json_encode([
                 "error" => 0,
                 "epg" => [
                     "1" => [
                         [], []
                     ],
                     "2" => [
                         []
                     ]
                 ]
             ])),
         )));

         $validator = new ValidatorWrapper($this->_clientAPI);
         $validator->checkChannelsEpg();

         $this->assertEquals([
             "epg.name" => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
             "epg.from" => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
             "epg.to"   => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
         ], $validator->getReport()['/channels/epg']);
     }

    public function testEpgWrongFieldTypes()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // epg is string
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "epg" => ""
            ])),

            // dictionary fields
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "epg" => [
                    "1" => "",
                    "2" => [
                        "",
                        [
                            "name" => 123,
                            "from" => "March 3, 2004, 5:15 pm",
                            "to" => "25.01.2003",
                            "url" => 0,
                            "torrent" => 1
                        ]
                    ],
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        // check epg is string
        $validator->checkChannelsEpg();

        // check dictionary fields
        $validator->checkChannelsEpg();

        $this->assertEquals([
            "epg" => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY, $validator::ERROR_TYPE_FIELD_MUSTBEARRAY, $validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "epg.name" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "epg.from" => [$validator::ERROR_TYPE_FIELD_MUSTBETIMESTAMP],
            "epg.to"   => [$validator::ERROR_TYPE_FIELD_MUSTBETIMESTAMP],
            "epg.url" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "epg.torrent" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
        ], $validator->getReport()['/channels/epg']);
    }
}
