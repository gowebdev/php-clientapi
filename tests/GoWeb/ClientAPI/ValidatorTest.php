<?php

namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkVodCategories() {
        $this->_checkVodCategories();
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

    public function testVodCategoriesValid()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // vod categories
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "categories" => [
                    [
                        "id" => 1,
                        "name" => "Films",
                        "genres" => [
                            ["id" => 1, "name" => 'Sci-fi'],
                            ["id" => 2, "name" => 'Action'],
                            ["id" => 3, "name" => 'Comedy'],
                        ],
                    ], [
                        "id" => 2,
                        "name" => "Series",
                    ]
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkVodCategories();
        $this->assertNull($validator->getReport()['/vod/genres']);
    }

    public function testVodCategoriesRequiredFields()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // required categories
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
            ])),

            // category dictionary required fields
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "categories" => []
            ])),

            // genres required fields
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "categories" => [
                    [
                        "id" => 1,
                        "name" => "Films",
                        "genres" => [[],[]],
                    ]
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // required categories
        $validator->checkVodCategories();

        // category dictionary required fields
        $validator->checkVodCategories();

        // genres required fields
        $validator->checkVodCategories();

        //var_dump($validator->getReport()['/vod/genres']);exit;

        $this->assertEquals([
            "categories"             => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "categories.id"          => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "categories.name"        => [$validator::ERROR_TYPE_FIELD_REQUIRED],
            "categories.genres.id"   => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
            "categories.genres.name" => [$validator::ERROR_TYPE_FIELD_REQUIRED, $validator::ERROR_TYPE_FIELD_REQUIRED],
        ], $validator->getReport()['/vod/genres']);
    }

    public function testVodCategoriesFieldsType()
    {
        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($this->_demoUser)),

            // categories must be array
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "categories" => ""
            ])),

            // set incorrect types to the dictionary fields
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode([
                "error" => 0,
                "categories" => [
                    [
                        "id" => "id",
                        "name" => 0,
                        "genres" => [
                            ["id" => "id", "name" => 1],
                            ""
                        ],
                    ], [
                        "id" => 2,
                        "name" => "Series",
                        "genres" => ""
                    ],
                    ""
                ]
            ])),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);

        // categories must be array
        $validator->checkVodCategories();

        // set incorrect types to the dictionary fields
        $validator->checkVodCategories();

        $this->assertEquals([
            "categories" => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY, $validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "categories.id" => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "categories.name" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
            "categories.genres" => [$validator::ERROR_TYPE_FIELD_MUSTBEARRAY, $validator::ERROR_TYPE_FIELD_MUSTBEARRAY],
            "categories.genres.id" => [$validator::ERROR_TYPE_FIELD_MUSTBEINT],
            "categories.genres.name" => [$validator::ERROR_TYPE_FIELD_MUSTBESTRING],
        ], $validator->getReport()['/vod/genres']);
    }

}
