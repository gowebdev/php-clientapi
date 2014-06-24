<?php 

namespace GoWeb\ClientAPI\Adapter\Yii;

class ClientAPI extends \GoWeb\ClientAPI implements \IApplicationComponent
{
    private $_initialized = false;
    
    /**
     *
     * @var boolean is cache enebled
     */
    public $cache = false;
    
    public function init()
    {
        parent::init();
        
        $this->_initialized = true;
    }
    
    public function __set($name, $value)
    {
        switch($name) {
            // define server url
            case 'apiServerUrl':
                $this->setHost( $value );
                break;
            
            // define agent if specified
            case 'agent':
                $this->setAgent($value);
                break;
            
            // define cacher
            case 'cache':
                if($value) {
                    $this->setCacheAdapter(new ClientAPICache);
                }
                break;
                
            // define logger
            case 'logger':
                if($value) {
                    $this->setLogger(\Yii::app()->{$value});
                }
                break;
        }
    }
    
    public function exceptionHandler($event)
    {
        if(!($event->exception instanceof \GoWeb\ClientAPI\Request\Exception\OtherDeviceAuthrorized)) {
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