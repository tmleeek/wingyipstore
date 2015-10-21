<?php
class Wingyip_Recipe_Model_Mysql4_Associated extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct()
    {   
        $this->_init('recipe/associated', 'associated_id');
    }
}
