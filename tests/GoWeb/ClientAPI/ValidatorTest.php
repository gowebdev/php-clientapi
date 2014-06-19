<?php
namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkAuth() {
        $this->_checkAuth();
    }

    public function checkServices() {
        $this->_checkServices();
    }

    public function checkChannelsList() {
        $this->_checkChannelsList();
    }
    
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

    public function testAuthValidUser()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(
                [
                    "error" => 0,
                    "status" => 0,
                    "token" => "d41d8cd98f00b204e9800998ecf8427e",
                    "permid" => "asdas",
                    "profile" => [
                        "id" => 1,
                        "email" => "homer@goweb.com",
                        "hash" => "asasASDasjfasdn",
                        "last_name" => "Simpson",
                        "first_name" => "Homer",
                        "gender" => "MALE",
                        "status" => "ACTIVE",
                        "birthday" => "1965-10-01",
                        "tester" => 1,
                        "contract_number" => "0001234567"
                    ],
                    "balance" => [
                        "amount" => 55.9,
                        "currency" => "EUR"
                    ],
                    "baseServices" => [
                        [
                            "id" => 19040,
                            "custom_name" => "In hall",
                            "service_id" => 1,
                            "name" => "Advanced",
                            "ad" => 1,
                            "catchup" => 0,
                            "chargeoff_period" => "DAILY",
                            "stb" => [],
                            "additional" => [
                                [
                                    "id" => 36147,
                                    "service_id" => 4,
                                    "custom_name" => "Sci-Fi",
                                    "cost" => 0.12,
                                    "chargeoff_period" => "DAILY",
                                ]
                            ],
                            "cost" => 0.00,
                            "total_monthly_cost" => 0.23,
                            "total_cost" => "0.28",
                        ],
                        [
                            "id" => 36146,
                            "custom_name" => "In kitchen",
                            "service_id" => 3,
                            "name" => "Basic",
                            "chargeoff_period" => "DAILY",
                            "cost" => 0,
                            "total_monthly_cost" => 3.12,
                            "total_cost" => 0.2
                        ]
                    ],
                    "activeBaseService" => 36146,
                    "speed" => [],
                    "rechargePage" => "http://site.com/rechargePage",
                    "profilePage" => "http://site.com/profilePage",
                ]
            )),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();

        $this->assertNull($validator->getReport()['/users/authorize']);
    }

    public function testAuthMissedRequiredField()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(array("error" => 0))),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();

        $this->assertEquals([
            "status"            => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "token"             => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "profile"           => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "balance"           => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices"      => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "activeBaseService" => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "speed"             => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "rechargePage"      => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "profilePage"       => [$validator::ERROR_TYPE_FIELD_REQUIRED],
        ], $validator->getReport()['/users/authorize']);

    }

    public function testAuthMissedRequiredIncludesField()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(
                [
                    "error" => 0,
                    "status" => 0,
                    "token" => "d41d8cd98f00b204e9800998ecf8427e",
                    "activeBaseService" => 36146,
                    "speed" => [],
                    "rechargePage" => "",
                    "profilePage" => "",

                    "profile" => [],
                    "balance" => [],
                    "baseServices" => [
                        [],
                        ['additional' => []],
                        ['additional' => [""]],
                        ['additional' => [[""], [""]]],
                        ['additional' => [[""], [""], [""]]],
                    ],
                ]
            )),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();

        $this->assertEquals([
            "profile.id"                               => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "profile.email"                            => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "profile.hash"                             => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "profile.contract_number"                  => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "profile.status"                           => [$validator::ERROR_TYPE_FIELD_REQUIRED],

            "balance.amount"                           => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "balance.currency"                         => [$validator::ERROR_TYPE_FIELD_REQUIRED],

            "baseServices.id"                          => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.custom_name"                 => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.service_id"                  => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.name"                        => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.cost"                        => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.total_cost"                  => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.total_monthly_cost"          => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.chargeoff_period"            => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],

            "baseServices.additional.id"               => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.additional.service_id"       => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.additional.custom_name"      => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.additional.cost"             => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "baseServices.additional.chargeoff_period" => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],


        ], $validator->getReport()['/users/authorize']);
    }

        public function testAuthWrongFieldType()
        {
            // mock response
            $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
                // auth
                new \Guzzle\Http\Message\Response(200, array(
                    'Content-type' => 'application/json',
                ), json_encode(
                    [
                        "error" => 0,
                        "status" => "",
                        "token" => 2342,
                        "permid" => 123,
                        "profile" => "",
                        "balance" => "",
                        "baseServices" => "",
                        "activeBaseService" => "",
                        "speed" => 123,
                        "rechargePage" => 123,
                        "profilePage" => [],
                    ]
                )),
            )));

            $validator = new ValidatorWrapper($this->_clientAPI);
            $validator->checkAuth();

            $this->assertEquals([
                "status"            => [$validator::ERROR_TYPE_FIELD_MUSTBEINT, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
                "token"             => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
                "permid"            => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
                "profile"           => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
                "balance"           => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
                "baseServices"      => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
                "activeBaseService" => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
                "speed"             => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
                "rechargePage"      => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
                "profilePage"       => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            ], $validator->getReport()['/users/authorize']);
        }

    public function testAuthWrongIncludesFieldType ()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(
                [
                    "error" => 0,
                    "status" => 0,
                    "token" => "d41d8cd98f00b204e9800998ecf8427e",
                    "permid" => "asdas",
                    "activeBaseService" => 36146,
                    "speed" => [],
                    "rechargePage" => "http://site.com/rechargePage",
                    "profilePage" => "http://site.com/profilePage",

                    // includes
                    "profile" => [
                        "id" => "1",
                        "email" => [],
                        "hash" => 1234567,
                        "last_name" => 123,
                        "first_name" => [],
                        "gender" => 1,
                        "status" => 2,
                        "tester" => "false",
                        "contract_number" => 0001234567
                    ],
                    "balance" => [
                        "amount" => "amount",
                        "currency" => 123
                    ],
                    "baseServices" => [
                        [
                            "id" => "19040",
                            "custom_name" => ['name'],
                            "service_id" => "1",
                            "name" => 123,
                            "ad" => "",
                            "catchup" => "",
                            "chargeoff_period" => 1,
                            "stb" => "",
                            "additional" => "",
                            "cost" => "cost",
                            "total_monthly_cost" => "total_monthly_cost",
                            "total_cost" => "total_cost",
                        ],
                        [
                            "id" => 19040,
                            "custom_name" => 'name',
                            "service_id" => 1,
                            "name" => "name",
                            "ad" => 0,
                            "catchup" => false,
                            "chargeoff_period" => "daily",
                            "stb" => [],
                            "additional" => [
                                [
                                    "id" => "36147",
                                    "service_id" => "4",
                                    "custom_name" => ["Sci-Fi"],
                                    "cost" => "cost",
                                    "chargeoff_period" => 0,
                                ]
                            ],
                            "cost" => 0.00,
                            "total_monthly_cost" => "0.1",
                            "total_cost" => 0.28,
                        ],
                    ],
                ]
            )),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();

        $this->assertEquals([
            "profile.id"                               => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "profile.email"                            => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.hash"                             => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.last_name"                        => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.first_name"                       => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.gender"                           => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.contract_number"                  => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.status"                           => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "profile.tester"                           => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],

            "balance.amount"                           => [$validator::ERROR_TYPE_FIELD_MUSTBEFLOAT],
            "balance.currency"                         => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],

            "baseServices.id"                          => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "baseServices.custom_name"                 => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "baseServices.service_id"                  => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "baseServices.name"                        => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "baseServices.cost"                        => [$validator::ERROR_TYPE_FIELD_MUSTBEFLOAT],
            "baseServices.total_cost"                  => [$validator::ERROR_TYPE_FIELD_MUSTBEFLOAT],
            "baseServices.total_monthly_cost"          => [$validator::ERROR_TYPE_FIELD_MUSTBEFLOAT],
            "baseServices.chargeoff_period"            => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING, $validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "baseServices.additional"                  => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "baseServices.ad"                          => [$validator::ERROR_TYPE_FIELD_MUSTBEBOOL],
            "baseServices.catchup"                     => [$validator::ERROR_TYPE_FIELD_MUSTBEBOOL],
            "baseServices.stb"                         => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY],

            "baseServices.additional.id"               => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "baseServices.additional.service_id"       => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "baseServices.additional.custom_name"      => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "baseServices.additional.cost"             => [$validator::ERROR_TYPE_FIELD_MUSTBEFLOAT],
            "baseServices.additional.chargeoff_period" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],


        ], $validator->getReport()['/users/authorize']);
    }

        public function testAuthFieldOverMaxLength()
        {
            // mock response
            $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
                // auth
                new \Guzzle\Http\Message\Response(200, array(
                    'Content-type' => 'application/json',
                ), json_encode(
                    [
                        "error" => 0,
                        "status" => 0,
                        "token" => "d41d8cd98f00b204e9800998ecf8427e2",
                        "permid" => "asdas",
                        "profile" => [
                            "id" => 1,
                            "email" => "needmorethanfortycharacteristics@goweb.com",
                            "hash" => "asasASDasjfasdn",
                            "last_name" => "needmorethanthirtycharacteristics",
                            "first_name" => "needmorethanthirtycharacteristics",
                            "gender" => "MALE",
                            "status" => "ACTIVE",
                            "birthday" => "1965-10-01",
                            "tester" => 1,
                            "contract_number" => "0001234567"
                        ],
                        "balance" => [
                            "amount" => 55.9,
                            "currency" => "EUR"
                        ],
                        "baseServices" => [
                            [
                                "id" => 19040,
                                "custom_name" => "In hall",
                                "service_id" => 1,
                                "name" => "Advanced",
                                "ad" => 1,
                                "catchup" => 0,
                                "chargeoff_period" => "DAILY",
                                "stb" => [],
                                "additional" => [
                                    [
                                        "id" => 36147,
                                        "service_id" => 4,
                                        "custom_name" => "Sci-Fi",
                                        "cost" => 0.12,
                                        "chargeoff_period" => "DAILY",
                                    ]
                                ],
                                "cost" => 0.16,
                                "total_monthly_cost" => 0.23,
                                "total_cost" => 0.28,
                            ],
                            [
                                "id" => 36146,
                                "custom_name" => "In kitchen",
                                "service_id" => 3,
                                "name" => "Basic",
                                "chargeoff_period" => "DAILY",
                                "cost" => 0.2,
                                "total_monthly_cost" => 3.12,
                                "total_cost" => 0.2
                            ]
                        ],
                        "activeBaseService" => 36146,
                        "speed" => [],
                        "rechargePage" => "http://site.com/rechargePage",
                        "profilePage" => "http://site.com/profilePage",
                    ]
                )),
            )));

            $validator = new ValidatorWrapper($this->_clientAPI);
            $validator->checkAuth();

            $this->assertEquals([
                "token"              => [$validator::ERROR_TYPE_FIELD_OVERLENGTHLIMIT],
                "profile.email"      => [$validator::ERROR_TYPE_FIELD_OVERLENGTHLIMIT],
                "profile.last_name"  => [$validator::ERROR_TYPE_FIELD_OVERLENGTHLIMIT],
                "profile.first_name" => [$validator::ERROR_TYPE_FIELD_OVERLENGTHLIMIT],
            ], $validator->getReport()['/users/authorize']);
        }

        public function testAuthWrongDateFormat()
         {
             // mock response
             $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(

                 // timestamp
                 new \Guzzle\Http\Message\Response(200, array(
                     'Content-type' => 'application/json',
                 ), json_encode(
                     [
                         "error" => 0,
                         "profile" => [
                             "birthday" => time(),
                         ]
                     ]
                 )),

                 // correct date
                 new \Guzzle\Http\Message\Response(200, array(
                     'Content-type' => 'application/json',
                 ), json_encode(
                     [
                         "error" => 0,
                         "profile" => [
                             "birthday" => "1965-11-29",
                         ]
                     ]
                 )),

                 // unknown date format
                 new \Guzzle\Http\Message\Response(200, array(
                     'Content-type' => 'application/json',
                 ), json_encode(
                     [
                         "error" => 0,
                         "profile" => [
                             "birthday" => "June 4, 2009",
                         ]
                     ]
                 )),

                 // invalid date string
                 new \Guzzle\Http\Message\Response(200, array(
                     'Content-type' => 'application/json',
                 ), json_encode(
                     [
                         "error" => 0,
                         "profile" => [
                             "birthday" => "asdasdasd",
                         ]
                     ]
                 ))
             )));

             $validator = new ValidatorWrapper($this->_clientAPI);

             // timestamp
             $validator->checkAuth();

             // correct date
             $validator->checkAuth();

             // unknown date format
             $validator->checkAuth();

             // invalid date string
             $validator->checkAuth();

             $this->assertEquals(
                 [$validator::ERROR_TYPE_FIELD_WRONGDATEFORMAT, $validator::ERROR_TYPE_FIELD_WRONGDATEFORMAT],
                 $validator->getReport()['/users/authorize']['profile.birthday']
             );
         }

    public function testAuthFieldsOutOfRange()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode(
                [
                    "error" => 0,
                    "status" => 11,
                    "token" => "d41d8cd98f00b204e9800998ecf8427e",
                    "permid" => "asdas",
                    "profile" => [
                        "id" => 1,
                        "email" => "homer@goweb.com",
                        "hash" => "asasASDasjfasdn",
                        "last_name" => "Simpson",
                        "first_name" => "Homer",
                        "gender" => "gender",
                        "status" => "status",
                        "birthday" => "1965-10-01",
                        "tester" => 2,
                        "contract_number" => "0001234567"
                    ],
                    "balance" => [
                        "amount" => 55.9,
                        "currency" => "EUR"
                    ],
                    "baseServices" => [
                        [
                            "id" => 19040,
                            "custom_name" => "In hall",
                            "service_id" => 1,
                            "name" => "Advanced",
                            "ad" => 1,
                            "catchup" => 0,
                            "chargeoff_period" => "yearly",
                            "stb" => [],
                            "additional" => [
                                [
                                    "id" => 36147,
                                    "service_id" => 4,
                                    "custom_name" => "Sci-Fi",
                                    "cost" => 0.12,
                                    "chargeoff_period" => "daily",
                                ]
                            ],
                            "cost" => 0.16,
                            "total_monthly_cost" => 0.23,
                            "total_cost" => 0.28,
                        ],
                    ],
                    "activeBaseService" => 36146,
                    "speed" => [],
                    "rechargePage" => "http://site.com/rechargePage",
                    "profilePage" => "http://site.com/profilePage",
                ]
            )),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();

        $this->assertEquals([
            "status"                                    => [$validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "profile.gender"                            => [$validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "profile.status"                            => [$validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "profile.tester"                            => [$validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "baseServices.chargeoff_period"             => [$validator::ERROR_TYPE_FIELD_OUTOFRANGE],
            "baseServices.additional.chargeoff_period"  => [$validator::ERROR_TYPE_FIELD_OUTOFRANGE],
        ], $validator->getReport()['/users/authorize']);
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
