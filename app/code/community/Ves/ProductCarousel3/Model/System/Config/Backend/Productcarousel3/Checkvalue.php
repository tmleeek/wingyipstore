<?php

class Ves_ProductCarousel3_Model_System_Config_Backend_ProductCarousel3_Checkvalue extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave(){
        $value=$this->getValue();
        	if ((!is_numeric($value) && !empty($value)) || $value < 0) {				
        	    throw new Exception(Mage::helper('ves_productcarousel3')->__($this->getField_config()->label . ': Value must be numeric.'));
        	}
        return $this;
    }

}
