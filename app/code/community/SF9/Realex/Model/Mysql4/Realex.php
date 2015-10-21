<?php

class SF9_Realex_Model_Mysql4_Realex extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('realex/realex', 'realex_id');
    }
}