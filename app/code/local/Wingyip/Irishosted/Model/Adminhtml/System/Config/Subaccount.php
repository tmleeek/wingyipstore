<?php


class Wingyip_Irishosted_Model_Adminhtml_System_Config_Subaccount
{

	 
	public function toOptionArray(){
         
        $options = array(
            array('value' => Wingyip_Irishosted_Helper_Data::IRIS_SUB_ACCOUNT_INTERNET, 'label'=> Mage::helper('irishosted')->__('Internet')),
        );
		return $options;
	}
}
