<?php
class Wingyip_Recipe_Model_Mysql4_Cuisine_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    { 
        $this->_init('recipe/cuisine');
    }
}
