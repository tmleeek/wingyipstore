<?php

class Wingyip_Realex_Model_Realex extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('realex/realex');
    }    
}