<?php
namespace GoWeb\ClientAPI;

class ValidatorWrapper extends Validator
{
    public function checkAuth() {
        $this->_checkAuth();
    }
}

class ValidatorTest extends \Guzzle\Tests\GuzzleTestCase 
{
    protected $_clientAPI;

    public function setUp()
    {
        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'apiServerUrl'  => 'http://dkjglkdsfjgkldsfjgkldsfjglkdsfjglkdfsgjldksfjgkldfg.com/1.0/',
            'cacheAdapter' => new CacheAdapterMock,
        ));
    }
    /**
     * @expectedException \GoWeb\ClientAPI\Validator\Exception\UnknownHost
     */
    public function testSetUnexistedHost()
    {
        $validator = $this->_clientAPI->getValidator();
        $validator->isValid();
    }

    public function testAuthMissedRequiredField()
    {
        $client = array(
            "error" => 0,
            "token" => "5440644e27a3259da3feaab416f37caf",
        );

        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($client)),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();
        $reports = $validator->getReport();

        foreach ($reports as $report) {
            $this->assertEquals($report['status'][0], $validator::ERROR_TYPE_FIELD_REQUIRED);
        }
    }

    public function testAuthWrongFieldType()
    {
        $client = array(
            "error" => 0,
            "token" => 544064432423242,
            "status" => "",

        );

        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($client)),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();
        $reports = $validator->getReport();

        foreach ($reports as $report) {

            if (isset($report['token'])) {
                $this->assertEquals($report['token'][0], $validator::ERROR_TYPE_FIELD_MUSTBESTRING);
            }

            if (isset($report['status'])) {
                $this->assertEquals($report['status'][0], $validator::ERROR_TYPE_FIELD_MUSTBEINT);
                $this->assertEquals($report['status'][1], $validator::ERROR_TYPE_FIELD_OUTOFRANGE);
            }
        }
    }

    public function testAuthFieldOverMaxLength()
    {
        $client = array(
            "error" => 0,
            "profile" => array(
                "email" => "demoasdKAJS32lLKNjknsjasjdnasl@domain.com",
            ),

        );

        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($client)),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();
        $reports = $validator->getReport();

        foreach ($reports as $report) {
            $this->assertEquals($report['profile']['email'][0], $validator::ERROR_TYPE_FIELD_OVERLENGTHLIMIT);
        }
    }

    public function testAuthWrongDateFormat()
    {
        $client = array(
            "error" => 0,
            "profile" => array(
                "birthday" => "19651129",
            ),

        );

        // mock response
        $this->_clientAPI->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            // auth
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type' => 'application/json',
            ), json_encode($client)),
        )));

        $validator = new ValidatorWrapper($this->_clientAPI);
        $validator->checkAuth();
        $reports = $validator->getReport();

        foreach ($reports as $report) {
            $this->assertEquals($report['profile']['birthday'][0], $validator::ERROR_TYPE_FIELD_WRONGDATEFORMAT);
        }
    }
}
