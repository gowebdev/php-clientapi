<?php

namespace GoWeb\ClientAPI;

class ValidatorTest extends \Guzzle\Tests\GuzzleTestCase 
{
    protected $_clientAPI;


    public function setUp() {

        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://server.com/1.0/',
            'cacheAdapter' => new CacheAdapterMock,
        ));

        // auth
        $client = new \GoWeb\Api\Model\Client(array(
            "error" => 0,
            "permid" => "42e5d49c35880f016ff56f99fd7daa7d262ecee1140347144836574",
            "token" => "0a9373c2de453627ed5abf13e60f9359453984a4",
            "status" => 0,
            "balance" => array(
                "amount" => 995.77,
                "currency" => "EUR"
            ),
            "profile" => array(
                "id" => 36574,
                "email" => "user@example.com",
                "hash" => "c524486gcbb730dcf13d0f5f94655b591dffa32f",
                "last_name" => "Alex",
                "first_name" => "Boldwin",
                "gender" => "MALE",
                "birthday" => "1954-08-11",
                "contract_number" => "00036574",
                "status" => "ACTIVE",
                "tester" => 1
            ),
            "baseServices" => array(
                array(
                    "id" => 44170,
                    "service_id" => 9,
                    "name" => "Full",
                    "custom_name" => "Kitchen",
                    "cost" => 0.033,
                    "total_cost" => 0.033,
                    "status" => "ACTIVE",
                    "catchup" => 1,
                    "ad" => 0,
                    "stb" => array(),
                    "additional" => array(
                        array(
                            "id" => 56294,
                            "service_id" => 11,
                            "name" => "add1",
                            "cost" => 0
                        ),
                        array(
                            "id" => 56295,
                            "service_id" => 12,
                            "name" => "add2",
                            "cost" => 0
                        )
                    )
                )
            ),
            "activeBaseService" => 44170,
            "speed" => array(
                "120KB" => "120",
                "512KB" => "512",
                "1MB" => "1",
                "2MB" => "2",
                "8MB" => "8",
                "36MB" => "36"
            ),
            "rechargePage" => "https://server.com/recharge/methods/userid/38574?theme=empty",
            "profilePage" => "https://server.com/account/dashboard/client_id/36574",
            "time" => 1400879748
        ));

        $this->_clientAPI->setActiveUser($client);
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

    public function testCheckAuth() {
        $_status = array(
            0 => 'Authorization successfull',
            1 => 'Server error (generic)',
            2 => 'Wrong credentials',
            3 => 'Account blocked',
            4 => 'Email not confirmed yet',
            5 => 'Client version not supported',
            6 => 'No active service found (Client myst register some service in personal cabinet)'
        );

        $_gender = array('MALE','FEMALE');

        $_profileStatus = array(
            'ACTIVE',
            'SUSPENDED',
            'BLOCKED',
            'CLOSED'
        );

        // get response
        $response = $this->_clientAPI
            ->auth()
            ->getResponse();

        $this->assertEmpty($response);

        // status
        $this->assertArrayHasKey('status', $response);
        $this->assertInternalType('integer', $response['status']);
        $this->assertContains($response['status'], array_keys($_status));

        // token
        if (isset($response['token'])) {
            $this->assertInternalType('string', $response['token']);
            $this->assertLessThanOrEqual(strlen($response['token']), 32);
        }

        // permid
        if (isset($response['permid'])) {
            $this->assertInternalType('string', $response['permid']);
        }

        // profile dictionary
        if (isset($response['profile'])) {
            $profile = $response['profile'];

            // id
            $this->assertClassHasAttribute('id', $profile);
            $this->assertInternalType('integer', $profile->id);

            // email
            $this->assertClassHasAttribute('email', $profile);
            $this->assertInternalType('string', $profile->email);
            $this->assertLessThanOrEqual(strlen($profile->email), 40);

            // hash
            $this->assertClassHasAttribute('hash', $profile);
            $this->assertInternalType('string', $profile->hash);

            // last_name
            if ($profile->last_name) {
                $this->assertInternalType('string', $profile->last_name);
                $this->assertLessThanOrEqual(strlen($profile->last_name), 30);
            }

            // first_name
            if ($profile->first_name) {
                $this->assertInternalType('string', $profile->first_name);
                $this->assertLessThanOrEqual(strlen($profile->first_name), 30);
            }

            // gender
            if ($profile->gender) {
                $this->assertInternalType('string', $profile->gender);
                $this->assertContains($profile->gender, $_gender);
            }

            // birthday
            // @TODO: date validation format string (yyyy-mm-dd)

            // contract_number
            $this->assertClassHasAttribute('contract_number', $profile);
            $this->assertInternalType('string', $profile->contract_number);

            // status
            $this->assertClassHasAttribute('status', $profile);
            $this->assertInternalType('string', $profile->status);
            $this->assertContains($profile->status, $_profileStatus);

            // tester
            if (isset($profile->tester)) {
                $this->assertInternalType('integer', $profile->tester);
                $this->assertContains($profile->tester, array(0,1));
            }

        }
    }
}
