<?php


class Wingyip_Irishosted_Model_Adminhtml_System_Config_Hashingmethod
{

	 
	public function toOptionArray(){
         
        $options = array(
 
            array('value' => Wingyip_Irishosted_Helper_Data::HASH_METHOD_MD5, 'label'=> Mage::helper('irishosted')->__('MD5')),
 
            array('value' => Wingyip_Irishosted_Helper_Data::HASH_METHOD_SHA1, 'label'=> Mage::helper('irishosted')->__('SHA1')),
 
        );
		return $options;
	}
}
