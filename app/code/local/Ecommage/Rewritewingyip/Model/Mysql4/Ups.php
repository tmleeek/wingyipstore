<?php

class Ecommage_Rewritewingyip_Model_Mysql4_Ups extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('rewritewingyip/ups', 'ups_id');
    }
}
