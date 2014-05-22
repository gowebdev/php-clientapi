<?php

class Meta extends \Sokil\Rest\Client\Structure
{
    public function getIcon()
    {
        return $this->get('icon');
    }
    
    public function getName()
    {
        return $this->get('name');
    }
    
    public function getDescription($lang = null)
    {
        if($lang) {
            return $this->get('description');
        } else {
            return $this->get('description.' . $lang);
        }
    }
}