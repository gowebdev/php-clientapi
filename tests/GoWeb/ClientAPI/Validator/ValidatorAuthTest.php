<?php
namespace GoWeb\ClientAPI;

/**
 * @covers GoWeb\ClientAPI\Validator::_checkAuth()
 */
class ValidatorAuthTest extends \Guzzle\Tests\GuzzleTestCase
{
    protected $_clientAPI;

    protected $_status = array(
        0 => 'Authorization successfull',
        1 => 'Server error (generic)',
        2 => 'Wrong credentials',
        3 => 'Account blocked',
        4 => 'Email not confirmed yet',
        5 => 'Client version not supported',
        6 => 'No active service found (Client myst register some service in personal cabinet)',
        7 => 'passed service is wrong'
    );

    protected $_gender = array('MALE','FEMALE');

    protected   $_profileStatus = array(
        'ACTIVE',
        'SUSPENDED',
        'BLOCKED',
        'CLOSED'
    );

    protected $_response;

    public function setUp()
    {
        $demoClient = array(
            "error" => 0,
            "token" => "5440644e27a3259da3feaab416f37caf",
            "status" => 0,

            "profile" => array(
                "id" => 0,
                "email" => "demo@domain.com",
                "hash" => "asdKAJS32lLKNjknsjasjdnasls",
                "first_name" => "Гість",
                "last_name" => "",
                "gender" => "MALE",
                "birthday" => "1965-11-29",
                "contract_number" => "0",
                "status" => "ACTIVE"
            ),

            "balance" => array(
                "amount" => 15.29,
                "currency" => "EUR"
            ),

            "baseServices" => [
                [
                    "id" => 19040,
                    "custom_name" => "In hall",
                    "service_id" => 1,
                    "name" => "Advanced",
                    "cost" => "0.16",
                    "ad" => 1,
                    "catchup" => 0,
                    "stb" => [],

                    "additional" => [
                        "id" => 36147,
                        "service_id" => 4,
                        "name" => "Sci-Fi",
                        "cost" => "0.12"

                    ],
                    "total_cost" => 0.28
                ],
                [
                    "id" => 36146,
                    "custom_name" => "In kitchen",
                    "service_id" => 3,
                    "name" => "Basic",
                    "cost" => "0.2",
                    "total_cost" => 0.2
                ]
            ],
            "activeBaseService" => 36146,

            "speed" => array(
                "120KB" => "120 килобит в секунду",
                "512KB" => "512 килобит в секунду",
                "1MB" => "1 мегабит в секунду",
                "2MB" => "2 мегабита в секунду",
                "8MB" => "8 мегабит в секунду",
                "36MB" => "36 мегабит в секунду"
            ),

            "rechargePage" => "http://domain.com/rechargePage",
            "profilePage" => "http://domain.com/profilePage",
        );

        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://apiserver.com/1.0',
            'cacheAdapter'  => new \GoWeb\ClientAPI\CacheAdapterMock,
        ));

        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($demoClient)),
        )));

        // response
        $this->_response = $this->_clientAPI
            ->auth()
            ->getResponse()
            ->toArray();
    }

    public function testCheckGlobals()
    {
        // response
        $this->assertNotEmpty($this->_response);

        // status
        $this->assertArrayHasKey('status', $this->_response);
        $this->assertInternalType('integer', $this->_response['status']);
        $this->assertContains($this->_response['status'], array_keys($this->_status));

        // token
        $this->assertArrayHasKey('token', $this->_response);
        $this->assertInternalType('string', $this->_response['token']);
        $this->assertLessThanOrEqual(32, strlen($this->_response['token']));

        // permid
        if (isset($this->_response['permid'])) {
            $this->assertInternalType('string', $this->_response['permid']);
        }

        // profile
        $this->assertArrayHasKey('profile', $this->_response);

        // balance
        $this->assertArrayHasKey('balance', $this->_response);

        // baseServices
        $this->assertArrayHasKey('baseServices', $this->_response);

        // activeBaseService
        $this->assertArrayHasKey('activeBaseService', $this->_response);
        $this->assertInternalType('integer', $this->_response['activeBaseService']);

        // speed
        $this->assertArrayHasKey('speed', $this->_response);

        // rechargePage
        $this->assertArrayHasKey('rechargePage', $this->_response);
        $this->assertInternalType('string', $this->_response['rechargePage']);

        // profilePage
        $this->assertArrayHasKey('profilePage', $this->_response);
        $this->assertInternalType('string', $this->_response['profilePage']);

    }

    public function testCheckProfileDictionary()
    {
        $profile = $this->_response['profile'];

        // id
        $this->assertArrayHasKey('id', $profile);
        $this->assertInternalType('integer', $profile['id']);

        // email
        $this->assertArrayHasKey('email', $profile);
        $this->assertInternalType('string', $profile['email']);
        $this->assertLessThanOrEqual(40, strlen($profile['email']));

        // hash
        $this->assertArrayHasKey('hash', $profile);
        $this->assertInternalType('string', $profile['hash']);

        // last_name
        if (isset($profile['last_name'])) {
            $this->assertInternalType('string', $profile['last_name']);
            $this->assertLessThanOrEqual(30, strlen($profile['last_name']));
        }

        // first_name
        if (isset($profile['first_name'])) {
            $this->assertInternalType('string', $profile['first_name']);
            $this->assertLessThanOrEqual(30, strlen($profile['first_name']));
        }

        // gender
        if (isset($profile['gender'])) {
            $this->assertInternalType('string', $profile['gender']);
            $this->assertContains($profile['gender'], $this->_gender);
        }

        // birthday
        // type: string (yyyy-mm-dd), timestamp
        if (isset($profile['birthday'])) {
            if (is_string($profile['birthday'])) {
                $_date = explode('-', $profile['birthday']);
                $this->assertTrue(checkdate($_date[1], $_date[2], $_date[0]));
            } else {
                $this->assertInternalType('integer', $profile['birthday']);
            }
        }

        // contract_number
        $this->assertArrayHasKey('contract_number', $profile);
        $this->assertInternalType('string', $profile['contract_number']);

        // status
        $this->assertArrayHasKey('status', $profile);
        $this->assertInternalType('string', $profile['status']);
        $this->assertContains($profile['status'], $this->_profileStatus);

        // tester
        if (isset($profile['tester'])) {
            $this->assertInternalType('integer', $profile['tester']);
            $this->assertContains($profile['tester'], array(0,1));
        }
    }

    public function testCheckBalanceDictionary()
    {
        $balance = $this->_response['balance'];

        // amount
        $this->assertArrayHasKey('amount', $balance);
        $this->assertInternalType('float', $balance['amount']);

        // currency
        $this->assertArrayHasKey('currency', $balance);
        $this->assertInternalType('string', $balance['currency']);

    }
}