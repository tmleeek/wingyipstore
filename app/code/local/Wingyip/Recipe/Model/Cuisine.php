<?php 
class Wingyip_Recipe_Model_Cuisine extends Mage_Core_Model_Abstract{
    public function _construct()
    {
        parent::_construct();
        $this->_init('recipe/cuisine');
    }
}
