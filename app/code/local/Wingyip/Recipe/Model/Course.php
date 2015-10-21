<?php 
class Wingyip_Recipe_Model_Course extends Mage_Core_Model_Abstract{
    public function _construct()
    {
        //exit('called');
        parent::_construct();
        $this->_init('recipe/course');
    }
}
