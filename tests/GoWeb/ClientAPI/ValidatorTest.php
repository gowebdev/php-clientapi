<?php

namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkServices() {
        $this->_checkServices();
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
        $validator = $this->_clientAPI->getValidator();
        $validator->isValid();
    }

    public function testServicesValidFields()
    {
        $services =   [
            "error" => 0,
            "packets" => [
                [
                    "id" => 9,
                    "name" => "Домашний",
                    "cost" => "0.99",
                    "type" => "BASE",
                    "channels" => [
                        1,
                        2,
                    ]
                ], [
                    "id" => 6,
                    "name" => "Рекламний",
                    "cost" => 0,
                    "type" => "BASE",
                    "channels" => [1]
                ]
            ],
            "channels" => [
                [
                    "id" => 1,
                    "name" => "Zero",
                    "logo" => "http://goods.ytv.su/logos/68x48/zero.png"
                ],[
                    "id" => 2,
                    "name" => "Channel France",
                    "logo" => "http://service.com/logos/68x48/cf.png"
                ]
            ],
            "time" => 1402909738
        ];

        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // services
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($services)),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkServices();
        $this->assertNull($validator->getReport()['/services']);
    }

    public function testServicesRequiredFields()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // incorrect data services
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(array('error' => 0))),

            // empty arrays services
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                'error' => 0,
                'packets' => [""],
                'channels' => [""],
            ])),

        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // incorrect data
        $validator->checkServices();

        // empty arrays
        $validator->checkServices();

        $this->assertEquals([
            'packets'          => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'packets.id'       => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'packets.name'     => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'packets.cost'     => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'packets.type'     => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'packets.channels' => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'channels'         => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'channels.id'      => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'channels.name'    => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            'channels.logo'    => [$validator::ERROR_TYPE_FIELD_REQUIRED],
        ],$validator->getReport()['/services']);
    }

    function testServicesWrongFieldTypes()
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
            ), json_encode([
                'error' => 0,
                'packets' => "adas",
                'channels' => "adas",
            ])),

            // dictionaries
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "packets" => [
                    [
                        "id" => "123",
                        "name" => 1,
                        "cost" => "cost",
                        "type" => 0,
                        "channels" => ""
                    ], [
                        "id" => 1,
                        "name" => "Packet",
                        "cost" => 0.12,
                        "type" => "",
                        "channels" => []
                    ]
                ],
                "channels" => [
                    [
                        "id" => "0",
                        "name" => 1,
                        "logo" => 0
                    ], [
                        "id" => 2,
                        "name" => "name",
                        "logo" => "logo"
                    ],
                ],
            ])),

        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // globals
        $validator->checkServices();

        // dictionaries
        $validator->checkServices();


        $this->assertEquals([
            'packets'          => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            'packets.id'       => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            'packets.name'     => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            'packets.cost'     => [$validator::ERROR_TYPE_FIELD_MUSTBEFLOAT],
            'packets.type'     => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            'packets.channels' => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            'channels'         => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            'channels.id'      => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            'channels.name'    => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            'channels.logo'    => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
        ],$validator->getReport()['/services']);
    }
}
