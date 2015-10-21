<?php

class Wingyip_Recipe_Model_Mysql4_Image extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('recipe/image', 'image_id');
    }
	
}
