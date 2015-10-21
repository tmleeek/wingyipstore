<?php

class Wingyip_Realex_Model_Mysql4_Realex_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('realex/realex');
    }
}