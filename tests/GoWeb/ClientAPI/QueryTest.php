<?php

namespace GoWeb\ClientAPI;

class QueryTest extends \Guzzle\Tests\GuzzleTestCase {

    protected $_clientAPI;
    protected $_query;

    public function setUp() {

        // configure client api
        $this->_clientAPI = new \GoWeb\ClientAPI(array(
            'cacheAdapter' => new CacheAdapterMock,
        ));

        // define client to skip auth
        $this->_clientAPI->setActiveUser(new \GoWeb\Api\Model\Client);
    }

    public function testGetCachedWithModificationChecking_NotModified() {
        $responseStack = new \Guzzle\Plugin\Mock\MockPlugin;
        $this->_clientAPI->getConnection()->addSubscriber($responseStack);

        /**
         * Add responses to stack
         */
        // model response
        $modelResponse = new \Guzzle\Http\Message\Response(200);
        $modelResponse->setBody(json_encode(array(
            'error' => 0,
            'var'   => 'value'
        )));
        $responseStack->addResponse($modelResponse);

        // not modified response
        $notModifiedResponse = new \Guzzle\Http\Message\Response(304);
        $notModifiedResponse->addHeader('Date', gmdate('D, d M Y H:i:s', time()) . ' GMT');
        $notModifiedResponse->addHeader('Last-Modified', gmdate('D, d M Y H:i:s', time() - 1000) . ' GMT');
        $responseStack->addResponse($notModifiedResponse);

        /**
         * Request model
         */
        $query = new \GoWeb\ClientAPI\Query($this->_clientAPI);
        $model = $query
            ->setUrl('/resource')
            ->get()
            ->send();

        $this->assertInstanceOf('\GoWeb\Api\Model', $model);
        $this->assertEquals('value', $model->getParam('var'));

        /**
         * Request from cache
         */
        $query = new \GoWeb\ClientAPI\Query($this->_clientAPI);
        $model = $query
            ->setUrl('/resource')
            ->get()
            ->send();

        $this->assertInstanceOf('\GoWeb\Api\Model', $model);
        $this->assertEquals('value', $model->getParam('var'));
    }
}
