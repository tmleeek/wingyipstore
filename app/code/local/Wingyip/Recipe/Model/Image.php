<?php 
class Wingyip_Recipe_Model_Image extends Mage_Core_Model_Abstract{ 
    public function _construct()
    {
        parent::_construct();
        $this->_init('recipe/image');
    }

    public function getImageCollection($pressId){
       return Mage::getModel('recipe/image')->getCollection()
        ->addFieldToFilter('recipe_id', $pressId)
        ->addFieldToFilter('status',1);
    }
}
