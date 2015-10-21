<?php 
class Wingyip_Recipe_Model_Category extends Mage_Core_Model_Abstract{
    public function _construct()
    {
        //exit('called');
        parent::_construct();
        $this->_init('recipe/category');
    }
}
