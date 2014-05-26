<?php 

namespace GoWeb\ClientAPI\Adapter\Yii;

class ClientAPI  extends \GoWeb\ClientAPI  implements \IApplicationComponent
{
    private $_initialized = false;
    
    public $apiServerUrl;
    
    public $agent;
    
    public $logger;
    
    /**
     *
     * @var boolean is cache enebled
     */
    public $cache = false;
    
    public function init()
    {        
        $this->_initialized = true;
        
        // define server url
        $this->setAPIServerUrl( $this->apiServerUrl );
        
        // define agent if specified
        if($this->agent) {
            $this->setAgent($this->agent);
        }
        
        // define cacher
        if($this->cache) {
            $this->setCacheAdapter(new ClientAPICache);
        }
        
        // define logger
        if($this->logger) {
            $this->setLogger(\Yii::app()->{$this->logger});
        }
    }  
    
    public function exceptionHandler($event)
    {
        if(!($event->exception instanceof \GoWeb\ClientAPI\Query\Exception\OtherDeviceAuthrorized)) {
            return;
        }
            
        $event->handled = true;

        // logout if not remembered
        // ask to relogin if remembered
        if(\Yii::app()->user->isAuthRemembered())
        {
            // close session to forse relogin from permanent id
            \Yii::app()->session->destroy();
        }
        else
        {
            // fully logout
            \Yii::app()->user->logout();
        }
        
        if(\Yii::app()->request->isAjaxRequest)
        {
            \Yii::app()->response->authRequired = 1;
            \Yii::app()->response->userRemembered = (int) \Yii::app()->user->isAuthRemembered();
            \Yii::app()->response->raiseError();
            \Yii::app()->response->sendJson();
        }
        else
        {
            // session may be stoped due to logout on case when auth not remembered
            \Yii::app()->session->open();
            
            \Yii::app()->user->setFlash('auth-form-message', _( 'This account used on another device. You need to sign-in again on this device in order to continue using service.' ) );
            \Yii::app()->user->loginRequired();
        }  
    }
    
    public function getIsInitialized() 
    {
        return $this->_initialized;
    }
}