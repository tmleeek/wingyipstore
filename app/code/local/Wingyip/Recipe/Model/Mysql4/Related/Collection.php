<?php
class Wingyip_Recipe_Model_Mysql4_Related_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    { 
        //parent::__construct();
        $this->_init('recipe/related');
    }
}