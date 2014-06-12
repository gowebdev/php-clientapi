<?php

namespace GoWeb\ClientAPI;

/**
 * @method \Sokil\Rest\Client\Request getOwner() get request object
 */
class RequestBehavior extends \Sokil\Rest\Client\Behavior
{    
    public function getValidateErrors() 
    {
        $response = $this->getOwner()->getResponse()->getStructure();
        if(!is_array($response->validate_errors)) {
            return array();
        }
        
        return $response->validate_errors;
    }
}
